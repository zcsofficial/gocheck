<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$notification_id = $_POST['id'];

$conn->begin_transaction();

try {
    $query = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Notification not found.");
    }

    $stmt->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Notification deleted']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
exit();
?>
