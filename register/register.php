<?php
// Mengimpor koneksi database
include('../helper/db.php');

// Fungsi untuk meng-upload foto
function uploadPhoto($photoInputName) {
    // Cek apakah ada file yang di-upload
    if (isset($_FILES[$photoInputName]) && $_FILES[$photoInputName]['error'] === UPLOAD_ERR_OK) {
        // Tentukan direktori penyimpanan foto
        $uploadDir = '../assets/';
        
        // Ambil informasi file
        $fileTmpPath = $_FILES[$photoInputName]['tmp_name'];
        $fileName = $_FILES[$photoInputName]['name'];
        $fileSize = $_FILES[$photoInputName]['size'];
        $fileType = $_FILES[$photoInputName]['type'];
        
        // Ekstensi file yang diperbolehkan
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        
        // Validasi ekstensi file
        if (!in_array(strtolower($fileExt), $allowedExts)) {
            return false; // File tidak valid
        }

        // Cek ukuran file (maksimal 2MB)
        if ($fileSize > 2 * 1024 * 1024) {
            return false; // File terlalu besar
        }

        // Generate nama file unik untuk menghindari nama yang sama
        $newFileName = uniqid() . '.' . $fileExt;
        $destPath = $uploadDir . $newFileName;

        // Pindahkan file dari folder sementara ke folder upload
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return $newFileName; // Kembalikan nama file yang berhasil di-upload
        } else {
            return false; // Gagal meng-upload file
        }
    }
    return false; // Tidak ada file yang di-upload
}

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

// Proses upload foto
$photoFileName = uploadPhoto('photo'); // Foto untuk peserta
$couplePhotoFileName = null;

if ($registrationType === 'couple') {
    $couplePhotoFileName = uploadPhoto('couplePhoto'); // Foto untuk pasangan
}

// Proses penyimpanan data ke database
$query = "INSERT INTO users (username, gender, age, photo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssis", $username, $gender, $age, $photoFileName);
$stmt->execute();
$maleUserId = $stmt->insert_id; // ID pengguna pertama (male)

// Jika tipe pendaftaran adalah pasangan, simpan data pasangan ke tabel 'users' dan buat relasi di tabel 'matches'
if ($registrationType === 'couple' && $coupleData) {
    $coupleUsername = $coupleData['coupleUsername'];
    $coupleAge = $coupleData['coupleAge'];
    $coupleGender = $coupleData['coupleGender'];

    // Simpan pasangan ke tabel 'users' dengan foto pasangan
    $query = "INSERT INTO users (username, gender, age, photo) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssis", $coupleUsername, $coupleGender, $coupleAge, $couplePhotoFileName);
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
