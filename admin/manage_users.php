<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

$message = '';

// Handle delete user
if(isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Only admin can delete users
    if(isAdmin()) {
        $delete_query = "DELETE FROM users WHERE id = $user_id AND user_type != 'admin'";
        if(mysqli_query($conn, $delete_query)) {
            $message = displaySuccess("User deleted successfully.");
        } else {
            $message = displayError("Failed to delete user: " . mysqli_error($conn));
        }
    } else {
        $message = displayError("You do not have permission to delete users.");
    }
}

// Handle update user type
if(isset($_POST['update_type'])) {
    $user_id = (int)$_POST['user_id'];
    $user_type = sanitize($_POST['user_type']);
    
    // Only admin can change user types
    if(isAdmin()) {
        $update_query = "UPDATE users SET user_type = '$user_type' WHERE id = $user_id";
        if(mysqli_query($conn, $update_query)) {
            $message = displaySuccess("User type updated successfully.");
        } else {
            $message = displayError("Failed to update user type: " . mysqli_error($conn));
        }
    } else {
        $message = displayError("You do not have permission to change user types.");
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = $search ? "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%'" : "";

$count_query = "SELECT COUNT(*) as count FROM users $search_condition";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['count'];
$total_pages = ceil($total_records / $records_per_page);

$users_query = "SELECT * FROM users $search_condition ORDER BY created_at DESC LIMIT $offset, $records_per_page";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - FitZone</title>
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
        
        .search-form {
            margin-bottom: 20px;
        }
        
        .search-form input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-form button {
            padding: 8px 15px;
            background-color: #00c6ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f1f1f1;
            margin: 0 4px;
            border-radius: 4px;
        }
        
        .pagination a:hover {
            background-color: #ddd;
        }
        
        .pagination .current {
            background-color: #00c6ff;
            color: white;
        }
        
        @media (max-width: 768px) {
            .admin-nav ul {
                flex-direction: column;
            }
            .admin-nav ul li {
                margin-bottom: 10px;
            }
            .search-form input[type="text"] {
                width: 100%;
                margin-bottom: 10px;
            }
            table {
                display: block;
                overflow-x: auto;
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
            <h2>Manage Users</h2>
        </div>
        
        <?php echo $message; ?>
        
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo $search; ?>">
            <button type="submit">Search</button>
            <?php if($search): ?>
                <a href="manage_users.php" style="margin-left: 10px;">Clear</a>
            <?php endif; ?>
        </form>
        
        <div class="card">
            <?php if (mysqli_num_rows($users_result) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Username</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Full Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Email</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Phone</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Type</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Joined</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $user['id']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $user['username']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $user['full_name']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $user['email']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $user['phone'] ? $user['phone'] : 'N/A'; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php if (isAdmin()): ?>
                                        <form method="POST" action="" style="margin: 0;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="user_type" onchange="this.form.submit()">
                                                <option value="customer" <?php echo $user['user_type'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                <option value="staff" <?php echo $user['user_type'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                                <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_type" value="1">
                                        </form>
                                    <?php else: ?>
                                        <?php echo ucfirst($user['user_type']); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php if (isAdmin() && $user['user_type'] != 'admin'): ?>
                                        <!-- <a href="view_user.php?id=<?php echo $user['id']; ?>" style="margin-right: 10px;">View</a> -->
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" style="margin-right: 10px;">Edit</a>
                                        <a href="manage_users.php?delete=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    <?php else: ?>
                                        <!-- <a href="view_user.php?id=<?php echo $user['id']; ?>">View</a> -->
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&search=' . $search : ''; ?>">&laquo; First</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . $search : ''; ?>">&lsaquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . $search : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . $search : ''; ?>">Next &rsaquo;</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . $search : ''; ?>">Last &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>