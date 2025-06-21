<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg,rgb(176, 118, 237),rgb(23, 92, 211));
      color: #fff;
      padding: 40px;
      min-height: 100vh;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 2em;
    }

    p {
      text-align: center;
      margin-bottom: 10px;
      font-weight: bold;
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

    select, input[type="hidden"], button {
      padding: 6px 10px;
      border-radius: 5px;
      border: none;
      font-size: 0.95em;
      margin: 3px 0;
    }

    select {
      background-color: #fff;
      color: #333;
    }

    button {
      background-color: #ffffff;
      color: #6a11cb;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #d1c4e9;
    }

    form[action="exportUser.php"] {
      text-align: center;
      margin: 20px 0;
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
  </style>
</head>
<body>
  <a class="back-link" href="main.php">Back</a>
  <h2>Users</h2>

  <?php
    if (isset($_GET['message'])) {
        echo '<p style="color: lightgreen;">' . htmlspecialchars($_GET['message']) . '</p>';
    }
    if (isset($_GET['error'])) {
        echo '<p style="color: #ff9999;">' . htmlspecialchars($_GET['error']) . '</p>';
    }
  ?>

  <table>
    <tr>
      <th>No.</th>
      <th>User's ID</th>
      <th>Username</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Date of Birth</th>
      <th>Gender</th>
      <th>Email</th>
      <th>Telephone Number</th>
      <th>Role</th>
      <th>Change Role</th>
    </tr>

    <?php
      $conn = new mysqli("localhost", "root", "", "groupworkhelper");
      if ($conn->connect_error){
          die("Connection failed: " . $conn->connect_error);
      }

      $sql = "SELECT idUser, username, firstName, lastName, dob, gender, email, phoneNum, role FROM users";
      $result = $conn->query($sql);
      $serialNumber = 1;

      if ($result && $result->num_rows > 0){
          while($row = $result->fetch_assoc()){
              echo "<tr>";
              echo "<td>{$serialNumber}</td>";
              echo "<td>{$row["idUser"]}</td>";
              echo "<td>{$row["username"]}</td>";
              echo "<td>{$row["firstName"]}</td>";
              echo "<td>{$row["lastName"]}</td>";
              echo "<td>{$row["dob"]}</td>";
              echo "<td>{$row["gender"]}</td>";
              echo "<td>{$row["email"]}</td>";
              echo "<td>{$row["phoneNum"]}</td>";
              echo "<td>{$row["role"]}</td>";
              echo "<td>
                      <form method='POST' action='change_role.php'>
                          <input type='hidden' name='user_id' value='{$row["idUser"]}'>
                          <select name='new_role'>
                              <option value='Member'" . ($row["role"] === 'Member' ? " selected" : "") . ">Member</option>
                              <option value='Teacher'" . ($row["role"] === 'Teacher' ? " selected" : "") . ">Teacher</option>
                              <option value='Admin'" . ($row["role"] === 'Admin' ? " selected" : "") . ">Admin</option>
                          </select>
                          <button type='submit'>Update</button>
                      </form>
                    </td>";
              echo "</tr>";
              $serialNumber++;
          }
      } else {
          echo "<tr><td colspan='11'>No users found</td></tr>";
      }

      $conn->close();
    ?>
  </table>

  <form method="post" action="exportUser.php">
    <button type="submit">Export to CSV</button>
  </form>

  <a href="main.php"><button class="back">Home</button></a>
</body>
</html>
