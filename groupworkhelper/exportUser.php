    <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "groupworkhelper";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Username', 'First Name', 'Last Name', 'Date of Birth', 'Gender', 'Email', 'Phone Number']);

        $sql = "SELECT idUser, username, firstName, lastName, dob, gender, email, phoneNum FROM users";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        $conn->close();
        exit();
    ?>