<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <title>todo list</title>
</head>
<body>
    <h1>Todo List</h1>
    <!-- new task add part  -->
    <div class="card-header d-flex justify-content-end align-items-center">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-plus-circle"></i> Add Task
        </button>
    </div>
    <!-- all task showing part -->
    <div class="col-md">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Tasks</h5>
            </div>
            <!-- PHP code to handle pagination and fetch tasks -->
            <?php
                $limit = 3;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page = max($page, 1); // Ensure page is at least 1
                $offset = ($page - 1) * $limit;

                // Count total records
                $query = "SELECT COUNT(*) as total FROM task";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $total_items = $row['total'];
                $total_pages = ceil($total_items / $limit);
                $stmt->close();

                // Fetch paginated tasks
                $query = "SELECT * FROM task LIMIT ? OFFSET ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
            ?>
            <!-- starting the table part -->
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <!-- showing table header -->
                        <thead>
                            <tr>
                                <th>Task id</th>
                                <th>Task name</th>
                                <th>Task description</th>
                                <th>Task created at</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <!-- showing task lists -->
                        <tbody>
                            <?php
                            $tasks = $result->fetch_all(MYSQLI_ASSOC);
                            $i=1;
                            foreach($tasks as $task){
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $task['task_name']; ?></td>
                                <td><?php echo $task['task_description']; ?></td>
                                <td><?php echo $task['task_created_at']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($task['task_status']) {
                                            'pending' => 'warning',
                                            'completed' => 'success',
                                            'expired' => 'danger'
                                        };
                                    ?>">
                                    <?php echo $task['task_status']; ?>
                                </td>
                                <td>
                                    <!-- Edit button module -->
                                    <button type="button" class="btn btn-primary" 
                                    data-bs-toggle="modal" data-bs-target="#editModal" 
                                    data-task-id="<?php echo $task['task_id']; ?>" 
                                    data-task-name="<?php echo htmlspecialchars($task['task_name']); ?>" 
                                    data-task-desc="<?php echo htmlspecialchars($task['task_description']); ?>" 
                                    data-task-status="<?php echo $task['task_status']; ?>"
                                    >
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <!-- Delete button module -->
                                    <button type ="button" class="btn btn-danger" 
                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    data-task-id="<?php echo $task['task_id']; ?>"
                                    data-task-name="<?php echo htmlspecialchars($task['task_name']); ?>"
                                    data-task-desc="<?php echo htmlspecialchars($task['task_description']); ?>"
                                    data-task-status="<?php echo $task['task_status']; ?>"
                                    >
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
                <?php
                    // Pagination
                    echo '<nav aria-label="Page navigation" class="mt-4"><ul class="pagination justify-content-center">';

                    // Previous Button
                    if ($page > 1) {
                        $prev = $page - 1;
                        echo "<li class='page-item'><a class='page-link' href='?page=$prev'>Previous</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><span class='page-link'>Previous</span></li>";
                    }

                    // Page Numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = ($i == $page) ? "active" : "";
                        echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                    }

                    // Next Button
                    if ($page < $total_pages) {
                        $next = $page + 1;
                        echo "<li class='page-item'><a class='page-link' href='?page=$next'>Next</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><span class='page-link'>Next</span></li>";
                    }

                    echo "</ul></nav>";
                    echo "</div>";

                ?>
            </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="api/add.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="task_name" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="task_name" name="task_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="task_description" class="form-label">Task Description</label>
                            <textarea class="form-control" id="task_description" name="task_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <!-- <label for="task_status" class="form-label">Task Status</label> -->
                            <input type = "hidden" class="form-select" id="task_status" name="task_status" value="pending" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="api/add.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <div class="mb-3">
                            <input type="hidden" id="edit_task_id" name="task_id" value="<?php echo $task['task_id']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="task_name" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="task_name" name="task_name" value="<?php echo $task['task_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="task_description" class="form-label">Task Description</label>
                            <textarea class="form-control" id="task_description" name="task_description" rows="3"><?php echo $task['task_description']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="task_status" class="form-label">Task Status</label>
                            <select class="form-select" id="task_status" name="task_status" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="api/add.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <div class="mb-3">
                            <input type="hidden" id="delete_task_id" name="task_id" value="<?php echo $task['task_id']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="task_name" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="delete_task_name" name="task_name" value="<?php echo $task['task_name']; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="task_description" class="form-label">Task Description</label>
                            <textarea class="form-control" id="delete_task_description" name="task_description" rows="3" readonly><?php echo $task['task_description']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="task_status" class="form-label">Task Status</label>
                            <select class="form-select" id="delete_task_status" name="task_status" required disabled>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Delete Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const taskId = button.getAttribute('data-task-id');
                const taskName = button.getAttribute('data-task-name');
                const taskDesc = button.getAttribute('data-task-desc');
                const taskStatus = button.getAttribute('data-task-status');

                // Set values inside the modal
                editModal.querySelector('#edit_task_id').value = taskId;
                editModal.querySelector('#task_name').value = taskName;
                editModal.querySelector('#task_description').value = taskDesc;
                editModal.querySelector('#task_status').value = taskStatus;
            });
        }
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const taskId = button.getAttribute('data-task-id');
                const taskName = button.getAttribute('data-task-name');
                const taskDesc = button.getAttribute('data-task-desc');
                const taskStatus = button.getAttribute('data-task-status');

                // Set values inside the delete modal (use correct IDs!)
                deleteModal.querySelector('#delete_task_id').value = taskId;
                deleteModal.querySelector('#delete_task_name').value = taskName; // if you're just showing the name
                deleteModal.querySelector('#delete_task_description').value = taskDesc; // if you're just showing the description
                deleteModal.querySelector('#delete_task_status').value = taskStatus; // if you're just showing the status
            });

        }
    });
    </script>


</body>
</html>