<?php
require 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$error = '';
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if(isset($_POST['add'])) {
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    
    if(!empty($title)) {
        if (!preg_match("/^[\p{L}\p{N}\s\p{P}]+$/u", $title)) {
            $_SESSION['error'] = "Task title should contain valid characters";
            header("Location: dashboard.php");
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $description]);
    }
}

if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $status = clean($_POST['status']);
    
    if(!empty($title)) {
        if (!preg_match("/^[\p{L}\p{N}\s\p{P}]+$/u", $title)) {
            $_SESSION['error'] = "Task title should contain valid characters";
            header("Location: dashboard.php");
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, status=? WHERE id=? AND user_id=?");
        $stmt->execute([$title, $description, $status, $id, $user_id]);
    }
}

if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
    $stmt->execute([$id, $user_id]);
}

$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

$edit_task = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id=? AND user_id=?");
    $stmt->execute([$id, $user_id]);
    $edit_task = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div>Welcome, <?php echo $user_name; ?></div>
        <div>
            <a href="dashboard.php">Tasks</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-box">
            <h3><?php echo $edit_task ? 'Edit Task' : 'Add New Task'; ?></h3>
            <form method="POST">
                <?php if($edit_task): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_task['id']; ?>">
                <?php endif; ?>
                
                <input type="text" name="title" placeholder="Task Title" 
                       value="<?php echo $edit_task ? $edit_task['title'] : ''; ?>" required>
                
                <textarea name="description" rows="3" placeholder="Task Description"><?php echo $edit_task ? $edit_task['description'] : ''; ?></textarea>
                
                <?php if($edit_task): ?>
                <select name="status">
                    <option value="pending" <?php echo $edit_task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $edit_task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $edit_task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                <?php endif; ?>
                
                <button type="submit" name="<?php echo $edit_task ? 'update' : 'add'; ?>">
                    <?php echo $edit_task ? 'Update' : 'Add'; ?>
                </button>
                
                <?php if($edit_task): ?>
                    <a href="dashboard.php" style="margin-left:10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if(!empty($error)): ?>
            <div class="error" style="margin: 20px auto; max-width: 600px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <h3>Your Tasks (<?php echo count($tasks); ?>)</h3>
        
        <?php if(empty($tasks)): ?>
            <p>No tasks yet</p>
        <?php else: ?>
            <table class="table">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php foreach($tasks as $task): ?>
                <tr>
                    <td><?php echo $task['title']; ?></td>
                    <td><?php echo substr($task['description'], 0, 50); ?>...</td>
                    <td>
                        <span class="status <?php echo $task['status']; ?>">
                            <?php 
                            if($task['status'] == 'pending') echo 'Pending';
                            elseif($task['status'] == 'in_progress') echo 'In Progress';
                            else echo 'Completed';
                            ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($task['created_at'])); ?></td>
                    <td>
                        <a href="?edit=<?php echo $task['id']; ?>">Edit</a>
                        <a href="?delete=<?php echo $task['id']; ?>" onclick="return confirm('Delete task?')" style="color:red; margin-left:10px;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
