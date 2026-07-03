<?php
require __DIR__ . '/../vendor/autoload.php'; // pastikan path composer autoload benar

use Google\Auth\Credentials\ServiceAccountCredentials;

// Path ke file service account JSON
$serviceAccountPath = __DIR__ . '/../secrets/pushnotif-ee3d5-firebase-adminsdk-fbsvc-720cf337fe.json';

// Project ID Firebase Anda
$projectId = 'pushnotif-ee3d5';

// Ambil semua token user dari database
include 'config/database.php';
$sql = "SELECT token FROM tbl_user_token";
$res = mysqli_query($kon, $sql);

$tokens = [];
while ($row = mysqli_fetch_assoc($res)) {
    $tokens[] = $row['token'];
}
echo "Jumlah token: " . count($tokens) . PHP_EOL;

// Buat access token dari service account
$scopes = [
    'https://www.googleapis.com/auth/firebase.messaging'
];
$credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);
$accessToken = $credentials->fetchAuthToken()['access_token'];

// Endpoint FCM HTTP v1
$url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

// Kirim notifikasi ke setiap token
foreach ($tokens as $token) {
    $data = [
        "message" => [
            "token" => $token,
            "notification" => [
                "title" => "Peringatan Absen",
                "body" => "10 menit lagi absen akan ditutup. Segera lakukan absen sebelum terlambat!"
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json; UTF-8"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    $result = curl_exec($ch); 
    echo "Token: $token\nRespon FCM: $result\n"; // Tambahkan debug ini
    curl_close($ch); 
    // Hapus token jika UNREGISTERED
    $resArr = json_decode($result, true);
    if (isset($resArr['error']['details'][0]['errorCode']) && $resArr['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
        $stmt = $kon->prepare("DELETE FROM tbl_user_token WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "Token $token dihapus dari database karena UNREGISTERED.\n";
    }
    // Optional: log $result
}
echo "Notifikasi dikirim ke semua user."; 