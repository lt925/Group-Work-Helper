<?php
session_start();
$conn = new mysqli("localhost", "root", "", "groupworkhelper");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check login
if (!isset($_SESSION['users'])) {
    header("Location: login.html");
    exit();
}

$userId = $_SESSION['users']['idUser'];
$username = $_SESSION['users']['username'];
$role = $_SESSION['users']['role'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['complete_task_id'])) {
        $taskId = intval($_POST['complete_task_id']);
        $stmt = $conn->prepare("UPDATE task SET status = TRUE WHERE idTask = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
    }

    if (isset($_POST['new_task_project_id'], $_POST['new_task_name'], $_POST['new_task_due'])) {
        $projectId = intval($_POST['new_task_project_id']);
        $taskName = trim($_POST['new_task_name']);
        $dueDate = $_POST['new_task_due'];

        if ($taskName !== "" && $dueDate !== "") {
            $stmt = $conn->prepare("INSERT INTO task (idProject, taskName, dueDate) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $projectId, $taskName, $dueDate);
            $stmt->execute();
        }
    }

    if (isset($_POST['submit_project_id'])) {
        $projectId = intval($_POST['submit_project_id']);
        $stmt = $conn->prepare("UPDATE user_project SET submitted = TRUE WHERE idUser = ? AND idProject = ?");
        $stmt->bind_param("ii", $userId, $projectId);
        $stmt->execute();
    }

    if (isset($_POST['leave_group_id'])) {
        $groupId = intval($_POST['leave_group_id']);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <nav>
       <div class="header">
            <h2>Welcome back! <?php echo htmlspecialchars($username); ?>!</h2>
             <div class="role-box">
                <?php
                    if ($role === 'Teacher') {
                        echo "👨‍🏫 You are logged in as: Teacher";
                    } elseif ($role === 'Admin') {
                        echo "🛠️ You are logged in as: Admin";
                    } elseif ($role === 'Student') {
                        echo "🎓 You are logged in as: Student";
                    } else {
                        echo "👤 You are logged in as: Unknown";
                    }
                ?>
            </div>
        </div>
        <a href="logout.php"><button class="logout">Logout</button></a>
    </nav>

    <?php
    if ($role === 'Teacher') {
        echo '<a href="teacher_view.php"><button class="teacherView">Teacher View</button></a>';
    }

    if ($role === 'Admin') {
        echo '<a href="admin.php"><button class="adminPanel"</button></a>';
    }
    ?>

    <h3>Your Group(s):</h3>
    <?php
    $groupQuery = "
        SELECT g.idGroup, g.groupName 
        FROM groups g
        JOIN user_group ug ON g.idGroup = ug.idGroup
        WHERE ug.idUser = ?
    ";
    $stmt = $conn->prepare($groupQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $groupResult = $stmt->get_result();

    if ($groupResult->num_rows > 0) {
        while ($group = $groupResult->fetch_assoc()) {
            echo "<div class='group'>
                    <strong>Group:</strong> " . htmlspecialchars($group['groupName']) . "
                    <form method='POST'>
                        <input type='hidden' name='leave_group_id' value='" . $group['idGroup'] . "'>
                        <button type='submit' class='leave-btn'>Leave Group</button>
                    </form>
                </div>";
        }
    } else {
        echo "<p>You are not in any group yet.</p>";
    }
    ?>
    <a href="joinGroup.html"><button>Join a Group</button></a>
    <a href="createGroup.html"><button>Create a Group</button></a>

    <h3>Your Projects:</h3>
    <?php
    $projectQuery = "
        SELECT p.idProject, p.projectName, p.description, p.startDate, p.endDate, up.submitted
        FROM project p
        JOIN user_project up ON p.idProject = up.idProject
        WHERE up.idUser = ?
        ORDER BY p.startDate DESC
    ";
    $stmt = $conn->prepare($projectQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $projectResult = $stmt->get_result();

    if ($projectResult->num_rows > 0) {
        while ($project = $projectResult->fetch_assoc()) {
            $submitted = $project['submitted'] ? "<span class='submitted'>(Submitted)</span>" : "";

            echo "<div class='project-card'>
                <h4>" . htmlspecialchars($project['projectName']) . " $submitted</h4>
                <div class='description'>" . htmlspecialchars($project['description']) . "</div>
                <div class='dates'>Start: " . $project['startDate'] . " | End: " . $project['endDate'] . "</div>
                <strong>Task Board:</strong>
                <ul>";

            $taskStmt = $conn->prepare("SELECT idTask, taskName, dueDate, status FROM task WHERE idProject = ? ORDER BY dueDate ASC");
            $taskStmt->bind_param("i", $project['idProject']);
            $taskStmt->execute();
            $taskResult = $taskStmt->get_result();

            if ($taskResult->num_rows > 0) {
                while ($task = $taskResult->fetch_assoc()) {
                    $taskClass = $task['status'] ? "task-done" : "";
                    $status = $task['status'] ? "<span style='color:green;'>✅ Done</span>" : "<span style='color:#FF9800;'>⏳ Pending</span>";

                    echo "<li class='$taskClass'>" . htmlspecialchars($task['taskName']) . " - " . $task['dueDate'] . " - $status";
                    if (!$task['status']) {
                        echo "<form method='POST' style='display:inline'>
                                <input type='hidden' name='complete_task_id' value='" . $task['idTask'] . "'>
                                <button type='submit'>Mark Done</button>
                              </form>";
                    }
                    echo "</li>";
                }
            } else {
                echo "<li>No tasks available.</li>";
            }

            echo "</ul>
                <form method='POST'>
                    <input type='hidden' name='new_task_project_id' value='" . $project['idProject'] . "'>
                    Task: <input type='text' name='new_task_name' required>
                    Due: <input type='date' name='new_task_due' required>
                    <button type='submit'>Add Task</button>
                </form><br>";

            if (!$project['submitted']) {
                echo "<form method='POST'>
                        <input type='hidden' name='submit_project_id' value='" . $project['idProject'] . "'>
                        <button type='submit' class='turnin-btn'>Turn In</button>
                    </form>";
            }

            echo "</div>";
        }
    } else {
        echo "<p>You haven't joined any projects yet.</p>";
    }
    ?>
    <a href="createProject.html"><button>Create a Project</button></a>
    <a href="joinProject.html"><button>Join a Project</button></a>
</body>
</html>
