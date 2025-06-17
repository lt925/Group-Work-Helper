<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['users']['idUser'];

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['idProject'])) {
    echo json_encode(['success' => false, 'message' => 'Missing project ID']);
    exit();
}

$projectId = intval($data['idProject']);

// 检查是否已加入该项目
$checkQuery = "SELECT * FROM user_project WHERE idUser = ? AND idProject = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $userId, $projectId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already joined this project']);
    exit();
}

// 插入关联记录
$insertQuery = "INSERT INTO user_project (idUser, idProject) VALUES (?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("ii", $userId, $projectId);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Project joined successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Join failed: ' . $stmt->error]);
}

$conn->close();
?>
