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

    // Simpan relasi pasangan di tabel 'matches'
    $query = "INSERT INTO matches (male_user_id, female_user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $maleUserId, $femaleUserId);
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