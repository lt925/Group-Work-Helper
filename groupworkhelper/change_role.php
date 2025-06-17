<?php
session_start();

// 连接数据库
$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 验证是否为管理员
if (!isset($_SESSION['users']) || strtolower($_SESSION['users']['role']) !== 'admin') {
    header("Location: admin.php?error=Unauthorized+access");
    exit();
}

// 验证 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user_id'], $_POST['new_role'])) {
        header("Location: admin.php?error=Missing+data");
        exit();
    }

    $userId = intval($_POST['user_id']);
    $newRole = $_POST['new_role'];

    // 允许的角色
    $validRoles = ['Admin', 'Teacher', 'Member'];
    if (!in_array($newRole, $validRoles)) {
        header("Location: admin.php?error=Invalid+role+selected");
        exit();
    }

    // 执行更新语句
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE idUser = ?");
    if ($stmt) {
        $stmt->bind_param("si", $newRole, $userId);
        if ($stmt->execute()) {
            header("Location: admin.php?message=Role+updated+successfully");
            exit();
        } else {
            header("Location: admin.php?error=Update+failed");
            exit();
        }
    } else {
        header("Location: admin.php?error=Prepare+failed");
        exit();
    }
} else {
    header("Location: admin.php?error=Invalid+request+method");
    exit();
}
?>
