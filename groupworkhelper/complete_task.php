<?php
session_start();
header('Content-Type: application/json');

// 1. 用户是否登录
if (!isset($_SESSION['users'])) {
    echo json_encode(['success' => false, 'message' => 'You are not logged in.']);
    exit;
}

// 2. 数据库连接
$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// 3. 获取用户 ID 和任务 ID
$userId = $_SESSION['users']['idUser'];
$taskId = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;

// 4. 验证任务是否属于用户所在的项目
$check = $conn->prepare("
    SELECT t.taskName, t.dueDate
    FROM task t
    INNER JOIN project p ON t.idProject = p.idProject
    INNER JOIN user_project up ON p.idProject = up.idProject
    WHERE t.idTask = ? AND up.idUser = ?
");
$check->bind_param("ii", $taskId, $userId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or task not found.']);
    exit;
}

$task = $result->fetch_assoc();

// 5. 更新任务状态为已完成
$update = $conn->prepare("UPDATE task SET status = TRUE WHERE idTask = ?");
$update->bind_param("i", $taskId);
$update->execute();

// 6. 返回 JSON 成功响应
echo json_encode([
    'success' => true,
    'taskName' => htmlspecialchars($task['taskName'], ENT_QUOTES, 'UTF-8'),
    'dueDate'  => $task['dueDate']
]);
exit;
?>
