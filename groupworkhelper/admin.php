<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h2>Users</h2>

    <!-- ✅ 显示提示信息 -->
    <?php
    if (isset($_GET['message'])) {
        echo '<p style="color: green;">' . htmlspecialchars($_GET['message']) . '</p>';
    }
    if (isset($_GET['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_GET['error']) . '</p>';
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
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "groupworkhelper";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT idUser, username, firstName, lastName, dob, gender, email, phoneNum, role FROM users";
        $result = $conn->query($sql);

        if(!$result){
            die("Query failed: " . $conn->error);
        }

        $serialNumber = 1;

        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td>".$serialNumber."</td>";
                echo "<td>".$row["idUser"]."</td>";
                echo "<td>".$row["username"]."</td>";
                echo "<td>".$row["firstName"]."</td>";
                echo "<td>".$row["lastName"]."</td>";
                echo "<td>".$row["dob"]."</td>";
                echo "<td>".$row["gender"]."</td>";
                echo "<td>".$row["email"]."</td>";
                echo "<td>".$row["phoneNum"]."</td>";
                echo "<td>".$row["role"]."</td>";
                echo "<td>
                        <form method='POST' action='change_role.php'>
                            <input type='hidden' name='user_id' value='" . $row["idUser"] . "'>
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
