<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Task Progress</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, rgb(176, 118, 237), rgb(23, 92, 211));
      color: #fff;
      padding: 40px;
      min-height: 100vh;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 2em;
    }

    #searchInput {
      display: block;
      margin: 0 auto 20px auto;
      padding: 10px;
      width: 50%;
      font-size: 16px;
      border-radius: 6px;
      border: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
      margin-bottom: 20px;
    }

    th, td {
      padding: 12px 16px;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
    }

    th {
      background-color: rgba(255, 255, 255, 0.2);
    }

    tr:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .status-done {
      color: #7CFC00;
      font-weight: bold;
    }

    .status-overdue {
      color: #ff6666;
      font-weight: bold;
    }

    .status-pending {
      color: #ffcc00;
      font-weight: bold;
    }

    a.back-link {
      position: fixed;
      top: 20px;
      right: 20px;
      color: #fff;
      background-color: #7e57c2;
      padding: 8px 12px;
      border-radius: 6px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    a.back-link:hover {
      background-color: #673ab7;
    }

    .back {
      display: block;
      margin: 20px auto;
      padding: 10px 20px;
      background-color: transparent;
      border: 2px solid #fff;
      color: #fff;
      border-radius: 25px;
      transition: all 0.3s ease;
    }

    .back:hover {
      background-color: #fff;
      color: #6a11cb;
    }
  </style>
</head>
<body>
  <a href="main.php" class="back-link">← Back</a>
  <h2>📋 Student Task Progress</h2>

  <input type="text" id="searchInput" placeholder="Search by student name...">

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
            echo "<tr><td colspan='5'>Database connection failed: " . htmlspecialchars($conn->connect_error) . "</td></tr>";
            exit();
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

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $username = htmlspecialchars($row['username']);
                $project = htmlspecialchars($row['projectName']);
                $task = htmlspecialchars($row['taskName']);
                $dueDate = htmlspecialchars($row['dueDate']);
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

  <a href="main.php"><button class="back">Home</button></a>

  <script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
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
