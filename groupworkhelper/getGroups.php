<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['users']['idUser'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['users']['idUser'];

$conn = new mysqli("localhost", "root", "", "groupworkhelper");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$sql = "
    SELECT g.idGroup, g.groupName
    FROM groups g
    JOIN user_group ug ON g.idGroup = ug.idGroup
    WHERE ug.idUser = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$groups = [];
while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}

echo json_encode(['success' => true, 'groups' => $groups]);
$conn->close();
?>
