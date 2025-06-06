<?php
require_once '../config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $action = $_POST['action'] ?? '';
    if($action === 'add')
    {
        $taskname = trim($_POST['task_name']);
        $taskdescription = trim($_POST['task_description']);
        $taskstatus = trim($_POST['task_status']);
        if(empty($taskname))
        {
            header('Location: ../index.php?error=Task name is required');
            exit;
        }
        $query = "INSERT INTO task (task_name, task_description, task_status) VALUES ('$taskname','$taskdescription', '$taskstatus')";
        $stmt = $conn->prepare($query);
        if($stmt->execute())
        {
            header('Location: ../index.php?success=Task added successfully');
        }
        else
        {
            header('Location: ../index.php?error=Failed to add task');
        }
    }
    else if($action === 'edit')
    {
        $taskid = $_POST['task_id'];
        $taskname = trim($_POST['task_name']);
        $taskdescription = trim($_POST['task_description']);
        $taskstatus = trim($_POST['task_status']);
        if(empty($taskname))
        {
            header('Location: ../index.php?error=Task name is required');
            exit;
        }
        $query = "UPDATE task SET task_name='$taskname', task_description='$taskdescription', task_status='$taskstatus' WHERE task_id='$taskid'";
        $stmt = $conn->prepare($query);
        if($stmt->execute())
        {
            header('Location: ../index.php?success=Task updated successfully');
        }
        else
        {
            header('Location: ../index.php?error=Failed to update task');
        }
    }
    else if($action === 'delete')
    {
        $taskid = $_POST['task_id'];
        $query = "DELETE FROM task WHERE task_id='$taskid'";
        $stmt = $conn->prepare($query);
        if($stmt->execute())
        {
            header('Location: ../index.php?success=Task deleted successfully');
        }
        else
        {
            header('Location: ../index.php?error=Failed to delete task');
        }
    }
    else
    {
        header('Location: ../index.php?error=Invalid action');
    }
}
?>
