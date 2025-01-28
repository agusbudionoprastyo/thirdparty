<?php
// Mengimpor koneksi database
include('db.php');

// Mengambil data yang dikirim dari form menggunakan metode POST atau JSON (tergantung cara Anda mengirimnya)
$data = json_decode(file_get_contents("php://input"), true);

// Mendapatkan data dari form
$registrationType = $data['registrationType'];
$username = $data['username'];  // Mengambil username
$age = $data['age'];            // Mengambil age
$gender = $data['gender'];      // Mengambil gender
$coupleData = $data['coupleData'] ?? null;

// Menyimpan data pengguna pertama (male) ke tabel 'users'
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

  // Menyimpan pasangan ke tabel 'users'
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

// Menyimpan transaksi ke tabel 'transactions' untuk pengguna pertama (male)
// Misalnya, untuk pendaftaran, item_id bisa di-set ke 1, quantity 1, total_amount 0, status 'pending'
$itemId = 1;  // Misalnya item_id untuk pendaftaran
$quantity = 1; // Jumlah item
$totalAmount = 0; // Untuk pendaftaran, misalnya tidak ada pembayaran (total_amount 0)
$status = 'pending'; // Status transaksi
$paymentMethod = 'manual'; // Metode pembayaran

// Menyimpan transaksi untuk pengguna pertama (male)
$query = "INSERT INTO transactions (transaction_date, user_id, item_id, quantity, total_amount, status, payment_method, created_at, updated_at) 
          VALUES (NOW(), ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiiiiss", $maleUserId, $itemId, $quantity, $totalAmount, $status, $paymentMethod);
$stmt->execute();

// Jika ada pasangan, simpan transaksi untuk pasangan (female)
if (isset($femaleUserId)) {
  // Menyimpan transaksi untuk pasangan (female)
  $stmt->bind_param("iiiiiss", $femaleUserId, $itemId, $quantity, $totalAmount, $status, $paymentMethod);
  $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'message' => 'Form submitted successfully']);
?>