<?php
require_once '../config/database.php';
echo "ok";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
        if ($taskId <= 0) {
            header('Location: ../index.php?error=Invalid task ID');
            exit;
        }
        $query = "DELETE FROM task WHERE task_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $taskId);
        if ($stmt->execute()) {
            header('Location: ../index.php?success=Task deleted successfully');
        } else {
            header('Location: ../index.php?error=Failed to delete task');
        }
    } else {
        header('Location: ../index.php?error=Invalid action');
    }
} else {
    header('Location: ../index.php?error=Invalid request method');
}
?>