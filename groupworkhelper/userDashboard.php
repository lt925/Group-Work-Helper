<?php
session_start();
$conn = new mysqli("localhost", "root", "", "groupworkhelper");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['users'])) {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION['users']['idUser'];
$username = $_SESSION['users']['username'];
$role = $_SESSION['users']['role'] ?? null;

// Flash message support
$message = '';
if (!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['complete_task_id'])) {
        $taskId = (int) $_POST['complete_task_id'];
        $stmt = $conn->prepare("UPDATE task SET status = TRUE WHERE idTask = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $_SESSION['message'] = "✅ Task marked as complete!";
    }

    if (!empty($_POST['new_task_project_id']) && !empty($_POST['new_task_name']) && !empty($_POST['new_task_due'])) {
        $projectId = (int) $_POST['new_task_project_id'];
        $taskName = trim($_POST['new_task_name']);
        $dueDate = $_POST['new_task_due'];

        if ($taskName !== "" && $dueDate !== "") {
            $stmt = $conn->prepare("INSERT INTO task (idProject, taskName, dueDate) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $projectId, $taskName, $dueDate);
            $stmt->execute();
        }
    }

    if (!empty($_POST['submit_project_id'])) {
        $projectId = (int) $_POST['submit_project_id'];
        $stmt = $conn->prepare("UPDATE user_project SET submitted = TRUE WHERE idUser = ? AND idProject = ?");
        $stmt->bind_param("ii", $userId, $projectId);
        $stmt->execute();
    }

    if (!empty($_POST['leave_group_id'])) {
        $groupId = (int) $_POST['leave_group_id'];

        $deleteProjects = $conn->prepare("
            DELETE up FROM user_project up
            JOIN project p ON p.idProject = up.idProject
            WHERE up.idUser = ? AND p.idGroup = ?
        ");
        $deleteProjects->bind_param("ii", $userId, $groupId);
        $deleteProjects->execute();

        $leaveGroup = $conn->prepare("DELETE FROM user_group WHERE idUser = ? AND idGroup = ?");
        $leaveGroup->bind_param("ii", $userId, $groupId);
        $leaveGroup->execute();
    }

    header("Location: main.php");
    exit();
}
?>

<!-- Add this just after the <body> tag or inside the container -->
<?php if (!empty($message)): ?>
    <div class="flash-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- CSS: Add this to your style section -->
<style>
.flash-message {
  background-color: #d4edda;
  color: #155724;
  padding: 12px;
  margin: 15px auto;
  text-align: center;
  border: 1px solid #c3e6cb;
  border-radius: 8px;
  width: 90%;
  animation: fadeOut 4s ease-in-out forwards;
}

@keyframes fadeOut {
  0% { opacity: 1; }
  80% { opacity: 1; }
  100% { opacity: 0; display: none; }
}
</style>
