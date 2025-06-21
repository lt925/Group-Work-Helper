<?php
session_start();

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function respondWithError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (empty($_SESSION['users']['idUser'])) {
    respondWithError("User not logged in", 401);
}

$userId = $_SESSION['users']['idUser'];

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respondWithError("Invalid JSON: " . json_last_error_msg());
}

$requiredFields = ['groupName', 'description', 'groupType'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        respondWithError("Missing required field: $field");
    }
}

try {
    $conn = new mysqli("localhost", "root", "", "groupworkhelper");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT idGroup FROM `groups` WHERE groupName = ?");
    $stmt->bind_param("s", $data['groupName']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        respondWithError("Group name already exists");
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO `groups` (groupName, description, groupType) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['groupName'], $data['description'], $data['groupType']);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create group: " . $stmt->error);
    }
    $groupId = $stmt->insert_id;
    $stmt->close();

    // Insert user as captain with full permissions
    $stmt = $conn->prepare("INSERT INTO user_group (idUser, idGroup, role, canAddTask, canDeleteTask) VALUES (?, ?, 'captain', 1, 1)");
    $stmt->bind_param("ii", $userId, $groupId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to add user to group: " . $stmt->error);
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Group created successfully with captain role',
        'groupId' => $groupId
    ]);
} catch (Exception $e) {
    respondWithError($e->getMessage(), 500);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}

file_put_contents("debug.log", print_r($_SESSION, true));
?>