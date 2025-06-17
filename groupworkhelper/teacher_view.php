<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="teacher_view.css">
</head>
<body>
<div class="header">
  <h1>📋 Student Task Progress</h1>

  <input type="text" id="searchInput" placeholder="Search by student name...">
</div>

  <table>
    <thead>
      <tr>
        <th>Student</th>
        <th>Project</th>
        <th>Task</th>
        <th>Due Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="taskTableBody">
      <?php
        session_start();
        if (!isset($_SESSION['users']) || !in_array($_SESSION['users']['role'], ['Teacher', 'Admin'])) {
            echo "<tr><td colspan='5'>Unauthorized access</td></tr>";
            exit();
        }

        $conn = new mysqli("localhost", "root", "", "groupworkhelper");
        if ($conn->connect_error) {
            die("<tr><td colspan='5'>Database connection failed: " . $conn->connect_error . "</td></tr>");
        }

        $query = "
          SELECT u.username, p.projectName, t.taskName, t.dueDate, t.status
          FROM user_project up
          JOIN users u ON up.idUser = u.idUser
          JOIN project p ON up.idProject = p.idProject
          JOIN task t ON t.idProject = p.idProject
          ORDER BY u.username, p.projectName, t.dueDate
        ";

        $result = $conn->query($query);
        $today = date('Y-m-d');

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $username = htmlspecialchars($row['username']);
                $project = htmlspecialchars($row['projectName']);
                $task = htmlspecialchars($row['taskName']);
                $dueDate = $row['dueDate'];
                $isDone = $row['status'];

                if ($isDone) {
                    $statusText = "✅ Done";
                    $statusClass = "status-done";
                } else if ($dueDate < $today) {
                    $statusText = "⚠️ Overdue";
                    $statusClass = "status-overdue";
                } else {
                    $statusText = "⏳ Pending";
                    $statusClass = "status-pending";
                }

                echo "<tr>
                        <td>$username</td>
                        <td>$project</td>
                        <td>$task</td>
                        <td>$dueDate</td>
                        <td class='$statusClass'>$statusText</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No task data found.</td></tr>";
        }

        $conn->close();
      ?>
    </tbody>
  </table>

  <a href="main.php"><button class="back_button">← Back to Main</button></a>

  <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll("#taskTableBody tr");
      rows.forEach(row => {
        const studentName = row.children[0].textContent.toLowerCase();
        row.style.display = studentName.includes(filter) ? "" : "none";
      });
    });
  </script>

</body>
</html>
