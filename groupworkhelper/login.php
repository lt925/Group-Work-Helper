<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// 使用 prepared statement 防止 SQL 注入
$stmt = $conn->prepare("SELECT idUser, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['users'] = [
            'idUser' => $user['idUser'],
            'username' => $user['username'],
            'role' => $user['role']   // ✅ 加上 role
        ];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
}












