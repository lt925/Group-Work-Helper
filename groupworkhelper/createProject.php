<?php
session_start();
header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['users']['idUser'];
$conn = new mysqli("localhost", "root", "", "groupworkhelper");

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$groupId = $_POST['IdGroup'] ?? null;
$projectName = trim($_POST['projectName'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate input
if (!$groupId || !$projectName || !$description) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Check if user is part of the group
$stmt = $conn->prepare("SELECT 1 FROM user_group WHERE idUser = ? AND idGroup = ?");
$stmt->bind_param("ii", $userId, $groupId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You are not a member of the selected group']);
    exit();
}
$stmt->close();

// Insert into `project`
$stmt = $conn->prepare("INSERT INTO project (projectName, description, idGroup) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $projectName, $description, $groupId);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to create project']);
    exit();
}

$projectId = $stmt->insert_id;
$stmt->close();

// Link user to project (optional)
$stmt = $conn->prepare("INSERT INTO user_project (idUser, idProject, submitted) VALUES (?, ?, 0)");
$stmt->bind_param("ii", $userId, $projectId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Project created']);
?>