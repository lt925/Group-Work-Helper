<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['users']['idUser'];
$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received or invalid JSON']);
    exit();
}

$projectName = trim($data['projectName']);
$description = trim($data['description']);
$startDate   = $data['startDate'];
$endDate     = $data['endDate'];
$IdGroup     = intval($data['IdGroup']);

if (!$projectName || !$startDate || !$endDate || !$IdGroup) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// 检查是否有重复的项目名称
$checkQuery = "SELECT 1 FROM project WHERE projectName = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $projectName);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Project name already exists']);
    exit();
}

// 插入新项目
$insertQuery = "INSERT INTO project (projectName, startDate, endDate, description, IdGroup) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("ssssi", $projectName, $startDate, $endDate, $description, $IdGroup);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to insert project: ' . $stmt->error]);
    exit();
}
$projectId = $stmt->insert_id;

// 将用户加入 user_project 表
$insertUserProject = "INSERT INTO user_project (idUser, idProject) VALUES (?, ?)";
$stmt = $conn->prepare($insertUserProject);
$stmt->bind_param("ii", $userId, $projectId);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to add user to project: ' . $stmt->error]);
    exit();
}

echo json_encode(['success' => true, 'projectId' => $projectId]);
$conn->close();
?>
