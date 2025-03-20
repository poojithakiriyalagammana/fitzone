<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

// Add new membership plan
if (isset($_POST['add_plan'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    
    $query = "INSERT INTO membership_plans (name, description, price, duration) 
              VALUES ('$name', '$description', $price, $duration)";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "New membership plan added successfully.";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Delete membership plan
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if plan is in use
    $check_query = "SELECT COUNT(*) as count FROM user_memberships WHERE plan_id = $id";
    $check_result = mysqli_query($conn, $check_query);
    
    // If table doesn't exist, create it
    if (!$check_result) {
        $create_table_query = "CREATE TABLE IF NOT EXISTS user_memberships (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            plan_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (plan_id) REFERENCES membership_plans(id) ON DELETE RESTRICT
        )";
        mysqli_query($conn, $create_table_query);
        $has_members = false;
    } else {
        $has_members = mysqli_fetch_assoc($check_result)['count'] > 0;
    }
    
    if ($has_members) {
        $error_message = "Cannot delete this membership plan because it is currently in use.";
    } else {
        $query = "DELETE FROM membership_plans WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success_message = "Membership plan deleted successfully.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}

// Update membership plan
if (isset($_POST['update_plan'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    
    $query = "UPDATE membership_plans SET 
              name = '$name', 
              description = '$description', 
              price = $price, 
              duration = $duration 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "Membership plan updated successfully.";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Get membership plans
$query = "SELECT * FROM membership_plans ORDER BY price";
$result = mysqli_query($conn, $query);
$plans = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get plan details for editing
$edit_plan = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $query = "SELECT * FROM membership_plans WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $edit_plan = mysqli_fetch_assoc($result);
}

// Get membership statistics
$total_plans = count($plans);
$lowest_price = $total_plans > 0 ? $plans[0]['price'] : 0;
$highest_price = $total_plans > 0 ? end($plans)['price'] : 0;
$avg_query = "SELECT AVG(price) as avg_price FROM membership_plans";
$avg_result = mysqli_query($conn, $avg_query);
$avg_price = mysqli_fetch_assoc($avg_result)['avg_price'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Memberships - FitZone</title>
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
        
        .admin-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: #fff;
            padding: 20px;
            margin-right: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-card:last-child {
            margin-right: 0;
        }
        
        .stat-card h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 24px;
            font-weight: bold;
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
            .stat-card {
                min-width: 100%;
                margin-right: 0;
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
            <h2>Manage Membership Plans</h2>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Plans</h3>
                <p><?php echo $total_plans; ?></p>
            </div>
            <div class="stat-card">
                <h3>Lowest Price</h3>
                <p>$<?php echo number_format($lowest_price, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Highest Price</h3>
                <p>$<?php echo number_format($highest_price, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Average Price</h3>
                <p>$<?php echo number_format($avg_price, 2); ?></p>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3><?php echo $edit_plan ? 'Edit Membership Plan' : 'Add New Membership Plan'; ?></h3>
            <form method="post" action="manage_membership.php">
                <?php if ($edit_plan): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_plan['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Plan Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $edit_plan ? $edit_plan['name'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo $edit_plan ? $edit_plan['description'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $edit_plan ? $edit_plan['price'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (days):</label>
                    <input type="number" id="duration" name="duration" min="1" value="<?php echo $edit_plan ? $edit_plan['duration'] : '30'; ?>" required>
                </div>
                
                <div class="form-group">
                    <?php if ($edit_plan): ?>
                        <button type="submit" name="update_plan" class="btn">Update Plan</button>
                        <a href="manage_membership.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_plan" class="btn">Add Plan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h3>All Membership Plans</h3>
            <?php if (count($plans) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr>
                                <td><?php echo $plan['name']; ?></td>
                                <td><?php echo substr($plan['description'], 0, 100) . (strlen($plan['description']) > 100 ? '...' : ''); ?></td>
                                <td>$<?php echo number_format($plan['price'], 2); ?></td>
                                <td><?php echo $plan['duration']; ?> days</td>
                                <td>
                                    <a href="manage_membership.php?edit=<?php echo $plan['id']; ?>" class="btn">Edit</a>
                                    <a href="manage_membership.php?delete=<?php echo $plan['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this membership plan?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No membership plans found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>