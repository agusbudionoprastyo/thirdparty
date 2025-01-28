<?php
// Mengimpor koneksi database
include('../helper/db.php');

// Mengambil data dari form menggunakan JSON
$data = json_decode(file_get_contents("php://input"), true);

// Cek validasi data JSON
if ($data === null) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid JSON input']));
}

// Ambil data dari form
$registrationType = $data['registrationType'];
$username = $data['username'];
$age = $data['age'];
$gender = $data['gender'];
$coupleData = $data['coupleData'] ?? null;

// Proses penyimpanan data ke database
$query = "INSERT INTO users (username, gender, age) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $username, $gender, $age);
$stmt->execute();
$maleUserId = $stmt->insert_id; // ID pengguna pertama (male)

// Jika ada foto yang diupload
if (isset($_FILES['photo'])) {
    $targetDir = "./assets/users/";
    $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        $uploadOk = 0;
        echo json_encode(['status' => 'error', 'message' => 'File is not an image.']);
    }

    // Cek apakah file sudah ada
    if (file_exists($targetFile)) {
        $uploadOk = 0;
        echo json_encode(['status' => 'error', 'message' => 'Sorry, file already exists.']);
    }

    // Cek ukuran file (misal: 5MB)
    if ($_FILES["photo"]["size"] > 5000000) {
        $uploadOk = 0;
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file is too large.']);
    }

    // Cek format file
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $uploadOk = 0;
        echo json_encode(['status' => 'error', 'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
    }

    // Cek jika $uploadOk set ke 0 karena ada error
    if ($uploadOk == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, your file was not uploaded.']);
    } else {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            // Menyimpan path foto ke tabel users
            $photoPath = "assets/users/" . basename($_FILES["photo"]["name"]);
            $query = "UPDATE users SET photo = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $photoPath, $maleUserId);
            $stmt->execute();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sorry, there was an error uploading your file.']);
        }
    }
}

// Jika tipe pendaftaran adalah pasangan, simpan data pasangan ke tabel 'users' dan buat relasi di tabel 'matches'
if ($registrationType === 'couple' && $coupleData) {
    $coupleUsername = $coupleData['coupleUsername'];
    $coupleAge = $coupleData['coupleAge'];
    $coupleGender = $coupleData['coupleGender'];

    // Simpan pasangan ke tabel 'users'
    $query = "INSERT INTO users (username, gender, age) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $coupleUsername, $coupleGender, $coupleAge);
    $stmt->execute();
    $femaleUserId = $stmt->insert_id; // ID pasangan (female)

    // Menyimpan data pasangan ke tabel 'matches'
    $maleVote = 'like'; // Default vote dari male
    $femaleVote = 'like'; // Default vote dari female
    $isMatch = 1; // Misalnya, cocok
    $sessionCompleted = 1; // Misalnya, sesi telah selesai

    // Query untuk memasukkan pasangan ke tabel 'matches'
    $query = "INSERT INTO matches (male_user_id, female_user_id, male_vote, female_vote, is_match, session_completed) 
                VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissii", $maleUserId, $femaleUserId, $maleVote, $femaleVote, $isMatch, $sessionCompleted);
    $stmt->execute();
}

// Menyimpan transaksi
$query = "INSERT INTO transactions (user_id, status) VALUES (?, 'pending')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $maleUserId);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Form submitted successfully']);
$stmt->close();
$conn->close();
?>