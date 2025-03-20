<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

// Add new class
if (isset($_POST['add_class'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $trainer = mysqli_real_escape_string($conn, $_POST['trainer']);
    
    $query = "INSERT INTO classes (name, description, schedule, trainer) 
              VALUES ('$name', '$description', '$schedule', '$trainer')";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "New class added successfully.";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Delete class
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if class exists and has no bookings
    $check_query = "SELECT COUNT(*) as count FROM bookings WHERE class_id = $id";
    $check_result = mysqli_query($conn, $check_query);
    $has_bookings = mysqli_fetch_assoc($check_result)['count'] > 0;
    
    if ($has_bookings) {
        $error_message = "Cannot delete class because it has active bookings.";
    } else {
        $query = "DELETE FROM classes WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success_message = "Class deleted successfully.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Update class
if (isset($_POST['update_class'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $trainer = mysqli_real_escape_string($conn, $_POST['trainer']);
    
    $query = "UPDATE classes SET 
              name = '$name', 
              description = '$description', 
              schedule = '$schedule', 
              trainer = '$trainer' 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "Class updated successfully.";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Get classes
$query = "SELECT * FROM classes ORDER BY name";
$result = mysqli_query($conn, $query);
$classes = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get class details for editing
$edit_class = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $query = "SELECT * FROM classes WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $edit_class = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - FitZone</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .admin-nav {
            background-color: #333;
            padding: 10px;
        }
        
        .admin-nav ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
        }
        
        .admin-nav ul li {
            margin-right: 20px;
        }
        
        .admin-nav ul li a {
            color: #fff;
            text-decoration: none;
        }
        
        .admin-nav ul li a:hover {
            color: #00c6ff;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-group textarea {
            height: 100px;
        }
        
        .btn {
            background-color: #00c6ff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-danger {
            background-color: #ff4d4d;
        }
        
        .btn-secondary {
            background-color: #555;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f2f2f2;
        }
        
        @media (max-width: 768px) {
            .admin-nav ul {
                flex-direction: column;
            }
            .admin-nav ul li {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-nav">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_queries.php">Manage Queries</a></li>
            <li><a href="manage_bookings.php">Manage Bookings</a></li>
            <li><a href="manage_classes.php">Manage Classes</a></li>
            <li><a href="manage_membership.php">Manage Memberships</a></li>
            <li><a href="../index.php">View Website</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-container">
        <div class="admin-header">
            <h2>Manage Classes</h2>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3><?php echo $edit_class ? 'Edit Class' : 'Add New Class'; ?></h3>
            <form method="post" action="manage_classes.php">
                <?php if ($edit_class): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_class['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Class Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $edit_class ? $edit_class['name'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo $edit_class ? $edit_class['description'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="schedule">Schedule:</label>
                    <input type="text" id="schedule" name="schedule" value="<?php echo $edit_class ? $edit_class['schedule'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="trainer">Trainer:</label>
                    <input type="text" id="trainer" name="trainer" value="<?php echo $edit_class ? $edit_class['trainer'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <?php if ($edit_class): ?>
                        <button type="submit" name="update_class" class="btn">Update Class</button>
                        <a href="manage_classes.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_class" class="btn">Add Class</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h3>All Classes</h3>
            <?php if (count($classes) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Schedule</th>
                            <th>Trainer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo $class['name']; ?></td>
                                <td><?php echo substr($class['description'], 0, 100) . (strlen($class['description']) > 100 ? '...' : ''); ?></td>
                                <td><?php echo $class['schedule']; ?></td>
                                <td><?php echo $class['trainer']; ?></td>
                                <td>
                                    <a href="manage_classes.php?edit=<?php echo $class['id']; ?>" class="btn">Edit</a>
                                    <a href="manage_classes.php?delete=<?php echo $class['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this class?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No classes found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>