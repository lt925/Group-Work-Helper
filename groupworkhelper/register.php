<?php
header('Content-Type: application/json');

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "groupworkhelper";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

// 提取前端数据 + 安全处理
$username   = $conn->real_escape_string($data['username']);
$password   = password_hash($data['password'], PASSWORD_DEFAULT);
$email      = $conn->real_escape_string($data['email']);
$phoneNum   = $conn->real_escape_string($data['phoneNum']);
$dob        = $conn->real_escape_string($data['dob']);
$gender     = $conn->real_escape_string($data['gender']);
$firstName  = $conn->real_escape_string($data['firstName']);
$lastName   = $conn->real_escape_string($data['lastName']);
$role   = $conn->real_escape_string($data['role']);

// 检查用户名是否已存在
$checkQuery = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit();
}

// 插入新用户
$insertQuery = "
    INSERT INTO users (username, password, email, phoneNum, dob, gender, firstName, lastName, role) VALUES (?, ?, ?,)
    VALUES ('$username', '$password', '$email', '$phoneNum', '$dob', '$gender', '$firstName', '$lastName','$role')
";

if ($conn->query($insertQuery) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
}

$conn->close();
