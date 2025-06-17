<?php
session_start();
header('Content-Type: application/json');

// 连接数据库
$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '❌ Database connection failed']);
    exit();
}

// 验证用户是否已登录
if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => '❌ User not logged in']);
    exit();
}
$userId = $_SESSION['users']['idUser'];

// 解析 JSON 数据
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['groupId'])) {
    echo json_encode(['success' => false, 'message' => '❌ Missing group ID']);
    exit();
}
$groupId = intval($data['groupId']);

// 检查是否已加入该群组
$check = $conn->prepare("SELECT 1 FROM user_group WHERE idUser = ? AND idGroup = ?");
$check->bind_param("ii", $userId, $groupId);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => '⚠️ You have already joined this group.']);
    exit();
}

// 插入 user_group 表
$stmt = $conn->prepare("INSERT INTO user_group (idUser, idGroup) VALUES (?, ?)");
$stmt->bind_param("ii", $userId, $groupId);

if ($stmt->execute()) {
    // 查找该组下的所有项目
    $projectQuery = $conn->prepare("SELECT idProject FROM project WHERE idGroup = ?");
    $projectQuery->bind_param("i", $groupId);
    $projectQuery->execute();
    $projectResult = $projectQuery->get_result();

    // 插入 user_project 表（避免重复）
    $insertProject = $conn->prepare("INSERT IGNORE INTO user_project (idUser, idProject) VALUES (?, ?)");

    while ($row = $projectResult->fetch_assoc()) {
        $projectId = $row['idProject'];
        $insertProject->bind_param("ii", $userId, $projectId);
        $insertProject->execute();
    }

    echo json_encode(['success' => true, 'message' => '✅ Group joined and projects assigned']);
} else {
    echo json_encode(['success' => false, 'message' => '❌ Failed to join group: ' . $stmt->error]);
}

$conn->close();
?>
