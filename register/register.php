<?php
// Mengimpor koneksi database
include('../helper/db.php');

// Fungsi untuk meng-upload foto
function uploadPhoto($photoInputName) {
    // Cek apakah ada file yang di-upload
    if (isset($_FILES[$photoInputName]) && $_FILES[$photoInputName]['error'] === UPLOAD_ERR_OK) {
        // Tentukan direktori penyimpanan foto
        $uploadDir = '../uploads/';
        
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

// Mengambil data dari form
$registrationType = $_POST['registrationType'];
$username = $_POST['username'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$phone = $_POST['phone'];
$email = $_POST['email'];

// Proses upload foto
$photoFileName = uploadPhoto('photo'); // Foto untuk peserta
$couplePhotoFileName = null;

if ($registrationType === 'couple') {
    $coupleUsername = $_POST['coupleUsername'];
    $coupleAge = $_POST['coupleAge'];
    $coupleGender = $_POST['coupleGender'];

    // Upload foto pasangan
    $couplePhotoFileName = uploadPhoto('couplePhoto');
}

// Proses penyimpanan data ke database
$query = "INSERT INTO users (username, gender, age, phone, email, photo) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssisss", $username, $gender, $age, $phone, $email, $photoFileName);
$stmt->execute();
$maleUserId = $stmt->insert_id; // ID pengguna pertama (male)

// Jika tipe pendaftaran adalah pasangan, simpan data pasangan ke tabel 'users' dan buat relasi di tabel 'matches'
if ($registrationType === 'couple') {
    // Simpan pasangan ke tabel 'users' dengan foto pasangan
    $query = "INSERT INTO users (username, gender, age, phone, email, photo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisss", $coupleUsername, $coupleGender, $coupleAge, $phone, $email, $couplePhotoFileName);
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
