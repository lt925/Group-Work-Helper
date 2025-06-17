<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '❌ Database connection failed']);
    exit();
}

if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => '❌ User not logged in']);
    exit();
}

$userId = $_SESSION['users']['idUser'];
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['groupId'])) {
    echo json_encode(['success' => false, 'message' => '❌ Missing group ID']);
    exit();
}

$groupId = intval($data['groupId']);

// 删除 user_group 记录
$deleteGroup = $conn->prepare("DELETE FROM user_group WHERE idUser = ? AND idGroup = ?");
$deleteGroup->bind_param("ii", $userId, $groupId);
$deleteGroup->execute();

// 获取 group 中的所有项目
$getProjects = $conn->prepare("SELECT idProject FROM project WHERE idGroup = ?");
$getProjects->bind_param("i", $groupId);
$getProjects->execute();
$projects = $getProjects->get_result();

// 从 user_project 删除
$removeProject = $conn->prepare("DELETE FROM user_project WHERE idUser = ? AND idProject = ?");
while ($row = $projects->fetch_assoc()) {
    $projectId = $row['idProject'];
    $removeProject->bind_param("ii", $userId, $projectId);
    $removeProject->execute();
}

echo json_encode(['success' => true, 'message' => '✅ Left group and removed from projects']);
$conn->close();
?>
