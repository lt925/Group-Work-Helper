<?php
session_start();
header('Content-Type: application/json');

// 确保用户已登录
if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}
$userId = $_SESSION['users']['idUser'];

// 连接数据库
$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// 解析前端 JSON 数据
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received or invalid JSON']);
    exit();
}

// 获取字段
$groupName = $data['groupName'];
$description = $data['description']; // 前端要确保是 groupDescription 才对
$groupType = $data['groupType'];

// 检查是否已存在同名群组
$checkQuery = "SELECT 1 FROM `groups` WHERE groupName = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $groupName);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Group name already exists']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// 插入群组
$insertQuery = "INSERT INTO `groups` (groupName, description, groupType, IdUser, createdAt) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("sssi", $groupName, $description, $groupType, $userId);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to create group: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}

$idGroup = $stmt->insert_id;
$stmt->close();

// 添加用户加入群组
$joinQuery = "INSERT INTO user_group (idUser, idGroup) VALUES (?, ?)";
$stmt = $conn->prepare($joinQuery);
$stmt->bind_param("ii", $userId, $idGroup);
$stmt->execute();
$stmt->close();

// 成功返回
echo json_encode(['success' => true]);
$conn->close();
?>
