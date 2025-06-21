<?php
// Enhanced security and error handling
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Database connection with improved error handling
try {
    $conn = new mysqli("localhost", "root", "", "groupworkhelper");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("System error. Please try again later.");
}

// Authentication check with redirect
if (!isset($_SESSION['users'])) {
    header("Location: index.html");
    exit();
}

// User data sanitization
$userId = filter_var($_SESSION['users']['idUser'], FILTER_VALIDATE_INT);
$username = htmlspecialchars($_SESSION['users']['username'] ?? '', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars($_SESSION['users']['role'] ?? 'User', ENT_QUOTES, 'UTF-8');

// Process POST requests with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Mark task as complete
        if (isset($_POST['complete_task_id'])) {
            $taskId = filter_var($_POST['complete_task_id'], FILTER_VALIDATE_INT);
            if ($taskId) {
                $stmt = $conn->prepare("UPDATE task SET status = TRUE WHERE idTask = ?");
                $stmt->bind_param("i", $taskId);
                $stmt->execute();
            }
        }

        // Add new task with validation
        if (isset($_POST['new_task_project_id'], $_POST['new_task_name'], $_POST['new_task_due'])) {
            $projectId = filter_var($_POST['new_task_project_id'], FILTER_VALIDATE_INT);
            $taskName = trim(htmlspecialchars($_POST['new_task_name'], ENT_QUOTES, 'UTF-8'));
            $dueDate = $_POST['new_task_due'];

            if ($projectId && $taskName !== "" && $dueDate !== "") {
                $stmt = $conn->prepare("INSERT INTO task (idProject, taskName, dueDate) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $projectId, $taskName, $dueDate);
                $stmt->execute();
            }
        }

        // Submit project
        if (isset($_POST['submit_project_id'])) {
            $projectId = filter_var($_POST['submit_project_id'], FILTER_VALIDATE_INT);
            if ($projectId) {
                $stmt = $conn->prepare("UPDATE user_project SET submitted = TRUE WHERE idUser = ? AND idProject = ?");
                $stmt->bind_param("ii", $userId, $projectId);
                $stmt->execute();
            }
        }

        // Leave group with transaction
        if (isset($_POST['leave_group_id'])) {
            $groupId = filter_var($_POST['leave_group_id'], FILTER_VALIDATE_INT);
            if ($groupId) {
                $conn->begin_transaction();
                try {
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

                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            }
        }

        header("Location: main.php");
        exit();
    } catch (Exception $e) {
        error_log("Error processing form: " . $e->getMessage());
        // Consider showing user-friendly error message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GroupWork Helper</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-light: #6366F1;
            --danger: #EF4444;
            --success: #10B981;
            --warning: #F59E0B;
            --dark: #1F2937;
            --light: #F9FAFB;
            --gray: #6B7280;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1),0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1),0 4px 6px -2px rgba(0,0,0,0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
            color: var(--dark);
            line-height: 1.6;
            padding: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            background-color: #E0E7FF;
            color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #DC2626;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gray);
            color: var(--dark);
        }

        .btn-outline:hover {
            background-color: #F3F4F6;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .task-list {
            list-style: none;
            margin: 1rem 0;
        }

        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .task-status {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-complete {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-overdue {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .form-control {
            padding: 0.5rem;
            border: 1px solid #D1D5DB;
            border-radius: var(--border-radius);
            width: 100%;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="user-info">
                <h1>Welcome back, <?= $username ?>!</h1>
                <span class="role-badge">
                    <?php switch($role): 
                        case 'Teacher': ?>👨‍🏫 Teacher<?php break; 
                        case 'Admin': ?>🛠️ Admin<?php break; 
                        case 'Student': ?>🎓 Student<?php break; 
                        default: ?>👤 User<?php endswitch; ?>
                </span>
            </div>
            <a href="logout.php" class="btn btn-outline">Logout</a>
        </header>

        <?php if ($role === 'Teacher'): ?>
            <div class="action-buttons">
                <a href="teacher_view.php" class="btn btn-primary">Teacher Dashboard</a>
            </div>
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
            <div class="action-buttons">
                <a href="admin.php" class="btn btn-primary">Admin Console</a>
            </div>
        <?php endif; ?>

        <section>
            <h2 class="section-title">Your Groups</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT g.idGroup, g.groupName 
                FROM groups g
                JOIN user_group ug ON g.idGroup = ug.idGroup
                WHERE ug.idUser = ?
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $groupResult = $stmt->get_result();

            if ($groupResult->num_rows > 0): ?>
                <div class="card">
                    <?php while ($group = $groupResult->fetch_assoc()): ?>
                        <div class="card-header">
                            <h3><?= htmlspecialchars($group['groupName']) ?></h3>
                            <form method="POST">
                                <input type="hidden" name="leave_group_id" value="<?= $group['idGroup'] ?>">
                                <button type="submit" class="btn btn-danger">Leave Group</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="card">You are not in any groups yet.</p>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="joinGroup.html" class="btn btn-primary">Join a Group</a>
                <a href="createGroup.html" class="btn btn-outline">Create New Group</a>
            </div>
        </section>

        <section>
            <h2 class="section-title">Your Projects</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT p.idProject, p.projectName, p.description, p.startDate, p.endDate, up.submitted
                FROM project p
                JOIN user_project up ON p.idProject = up.idProject
                WHERE up.idUser = ?
                ORDER BY p.startDate DESC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $projectResult = $stmt->get_result();

            if ($projectResult->num_rows > 0): ?>
                <?php while ($project = $projectResult->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($project['projectName']) ?>
                                <?php if ($project['submitted']): ?>
                                    <span class="status-complete task-status">Submitted</span>
                                <?php endif; ?>
                            </h3>
                            <?php if (!$project['submitted']): ?>
                                <form method="POST">
                                    <input type="hidden" name="submit_project_id" value="<?= $project['idProject'] ?>">
                                    <button type="submit" class="btn btn-success">Submit Project</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <p><?= htmlspecialchars($project['description']) ?></p>
                        <p><small>Timeline: <?= $project['startDate'] ?> to <?= $project['endDate'] ?></small></p>
                        
                        <h4>Tasks</h4>
                        <ul class="task-list">
                            <?php
                            $taskStmt = $conn->prepare("
                                SELECT idTask, taskName, dueDate, status 
                                FROM task 
                                WHERE idProject = ? 
                                ORDER BY status ASC, dueDate ASC
                            ");
                            $taskStmt->bind_param("i", $project['idProject']);
                            $taskStmt->execute();
                            $taskResult = $taskStmt->get_result();

                            if ($taskResult->num_rows > 0):
                                while ($task = $taskResult->fetch_assoc()): 
                                    $isOverdue = !$task['status'] && strtotime($task['dueDate']) < time();
                                    ?>
                                    <li class="task-item">
                                        <div>
                                            <?= htmlspecialchars($task['taskName']) ?>
                                            <small>(Due: <?= $task['dueDate'] ?>)</small>
                                        </div>
                                        <div>
                                            <?php if ($task['status']): ?>
                                                <span class="status-complete task-status">Completed</span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="status-overdue task-status">Overdue</span>
                                            <?php else: ?>
                                                <span class="status-pending task-status">Pending</span>
                                            <?php endif; ?>
                                            
                                            <?php if (!$task['status']): ?>
                                                <form method="POST" style="display: inline-block; margin-left: 0.5rem;">
                                                    <input type="hidden" name="complete_task_id" value="<?= $task['idTask'] ?>">
                                                    <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.5rem;">Complete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endwhile;
                            else: ?>
                                <li>No tasks created yet</li>
                            <?php endif; ?>
                        </ul>

                        <form method="POST" class="form-grid">
                            <input type="hidden" name="new_task_project_id" value="<?= $project['idProject'] ?>">
                            <input type="text" name="new_task_name" placeholder="Task name" class="form-control" required>
                            <input type="date" name="new_task_due" class="form-control" required>
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="card">You haven't joined any projects yet.</p>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="createProject.html" class="btn btn-primary">Create Project</a>
                <a href="joinProject.html" class="btn btn-outline">Join Project</a>
            </div>
        </section>
    </div>
</body>
</html>