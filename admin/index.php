<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

// Get some statistics
$users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'"))['count'];
$bookings_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$pending_queries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM queries WHERE status = 'pending'"))['count'];
$classes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM classes"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitZone</title>
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
            <h2>Admin Dashboard</h2>
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $users_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?php echo $bookings_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Queries</h3>
                <p><?php echo $pending_queries; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Classes</h3>
                <p><?php echo $classes_count; ?></p>
            </div>
        </div>
        
        <div class="card">
            <h3>Recent Bookings</h3>
            <?php
            // Get recent bookings
            $bookings_query = "SELECT b.*, u.username, c.name as class_name 
                              FROM bookings b 
                              JOIN users u ON b.user_id = u.id 
                              LEFT JOIN classes c ON b.class_id = c.id 
                              ORDER BY b.created_at DESC LIMIT 5";
            $bookings_result = mysqli_query($conn, $bookings_query);
            
            if (mysqli_num_rows($bookings_result) > 0):
            ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">User</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Class</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Time</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $booking['username']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $booking['class_name']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst($booking['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bookings found.</p>
            <?php endif; ?>
            <p><a href="manage_bookings.php" class="btn" style="display: inline-block; margin-top: 10px;">View All Bookings</a></p>
        </div>
        
        <div class="card">
            <h3>Recent Queries</h3>
            <?php
            // Get recent queries
            $queries_query = "SELECT q.*, u.username 
                             FROM queries q 
                             LEFT JOIN users u ON q.user_id = u.id 
                             ORDER BY q.created_at DESC LIMIT 5";
            $queries_result = mysqli_query($conn, $queries_query);
            
            if (mysqli_num_rows($queries_result) > 0):
            ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Subject</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($query = mysqli_fetch_assoc($queries_result)): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['name']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['subject']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('F j, Y', strtotime($query['created_at'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst($query['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No queries found.</p>
            <?php endif; ?>
            <p><a href="manage_queries.php" class="btn" style="display: inline-block; margin-top: 10px;">View All Queries</a></p>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>