<?php
// Mengimpor koneksi database
include('../helper/db.php');

// Fungsi untuk meng-upload foto
function uploadPhoto($photoInputName) {
    // Cek apakah ada file yang di-upload
    if (isset($_FILES[$photoInputName]) && $_FILES[$photoInputName]['error'] === UPLOAD_ERR_OK) {
        // Tentukan direktori penyimpanan foto
        $uploadDir = '../users/';
        
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

function generateRandomPassword() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Menghasilkan password 6 digit dengan padding jika kurang
}

// Fungsi untuk generate username
function generateUsername($name) {
    // Ambil 3 huruf pertama dari nama
    $usernameBase = substr($name, 0, 3);
    // Ubah menjadi huruf kecil
    $usernameBase = strtolower($usernameBase);
    // Tambahkan 3 angka acak
    $randomNumbers = rand(100, 999);
    return $usernameBase . $randomNumbers;
}

// Fungsi untuk generate username pasangan
function generateCoupleUsername($coupleName) {
    // Ambil 3 huruf pertama dari nama
    $usernameBase = substr($coupleName, 0, 3);
    // Ubah menjadi huruf kecil
    $usernameBase = strtolower($usernameBase);
    // Tambahkan 3 angka acak
    $randomNumbers = rand(100, 999);
    return $usernameBase . $randomNumbers;
}


// Mengambil data dari form
$registrationType = $_POST['registrationType'];
$name = $_POST['username'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$phone = $_POST['phone'];
$email = $_POST['email'];

// Generate username
$username = generateUsername($name);
$password = generateRandomPassword();

// Proses upload foto
$photoFileName = uploadPhoto('photo'); // Foto untuk peserta
$couplePhotoFileName = null;

if ($registrationType === 'couple') {
    $coupleName = $_POST['coupleUsername'];
    $coupleAge = $_POST['coupleAge'];
    $coupleGender = $_POST['coupleGender'];
    $coupleusername = generateCoupleUsername($coupleName);
    // Upload foto pasangan
    $couplePhotoFileName = uploadPhoto('couplePhoto');
}

// Proses penyimpanan data ke database
$query = "INSERT INTO users (name, gender, age, phone, email, photo, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssisssss", $name, $gender, $age, $phone, $email, $photoFileName, $username, $password);
$stmt->execute();
$maleUserId = $stmt->insert_id; // ID pengguna pertama (male)

// Jika tipe pendaftaran adalah pasangan, simpan data pasangan ke tabel 'users' dan buat relasi di tabel 'matches'
if ($registrationType === 'couple') {
    // Simpan pasangan ke tabel 'users' dengan foto pasangan
    $query = "INSERT INTO users (name, gender, age, phone, email, photo, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisssss", $coupleName, $coupleGender, $coupleAge, $phone, $email, $couplePhotoFileName, $coupleusername, $password);
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

// Mengambil harga item yang aktif dan memiliki stok lebih dari 0
$query = "SELECT price FROM items WHERE active = '1' AND stock > 0 LIMIT 1"; // Ambil satu item
$stmt = $conn->prepare($query);
$stmt->execute();
$stmt->bind_result($itemPrice);
$stmt->fetch();
$stmt->close();

// Jika tidak ada item yang aktif, tampilkan error
if (!$itemPrice) {
    echo json_encode(['status' => 'error', 'message' => 'Item tidak tersedia']);
    exit;
}

// Jika pendaftaran adalah pasangan, kalikan harga item dengan 2
$totalAmount = ($registrationType === 'couple') ? $itemPrice * 1 : $itemPrice;

// Mendapatkan transaction ID dari form
$transactionId = $_POST['transactionid'];

$query = "INSERT INTO transactions (transaction_id, user_id, total_amount, status) VALUES (?, ?, ?, 'pending')";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $transactionId, $maleUserId, $totalAmount);
$stmt->execute();

// Menutup koneksi
echo json_encode(['status' => 'success', 'message' => 'Form submitted successfully']);
$stmt->close();
$conn->close();
?>