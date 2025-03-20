<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

$message = '';

// Handle booking status updates
if(isset($_GET['confirm'])) {
    $booking_id = (int)$_GET['confirm'];
    $update_query = "UPDATE bookings SET status = 'confirmed' WHERE id = $booking_id";
    
    if(mysqli_query($conn, $update_query)) {
        $message = displaySuccess("Booking confirmed successfully.");
    } else {
        $message = displayError("Failed to confirm booking: " . mysqli_error($conn));
    }
}

if(isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id";
    
    if(mysqli_query($conn, $update_query)) {
        $message = displaySuccess("Booking cancelled successfully.");
    } else {
        $message = displayError("Failed to cancel booking: " . mysqli_error($conn));
    }
}

// Handle delete booking
if(isset($_GET['delete']) && isAdmin()) {
    $booking_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM bookings WHERE id = $booking_id";
    
    if(mysqli_query($conn, $delete_query)) {
        $message = displaySuccess("Booking deleted successfully.");
    } else {
        $message = displayError("Failed to delete booking: " . mysqli_error($conn));
    }
}

// Get bookings with filtering and pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = $status_filter ? "AND b.status = '$status_filter'" : "";

// Filter by class
$class_filter = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$class_condition = $class_filter ? "AND b.class_id = $class_filter" : "";

// Filter by date range
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$date_condition = '';

if($date_from && $date_to) {
    $date_condition = "AND b.booking_date BETWEEN '$date_from' AND '$date_to'";
} elseif($date_from) {
    $date_condition = "AND b.booking_date >= '$date_from'";
} elseif($date_to) {
    $date_condition = "AND b.booking_date <= '$date_to'";
}

// Search by user
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = $search ? "AND (u.username LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')" : "";

// Build the query
$count_query = "SELECT COUNT(*) as count 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                LEFT JOIN classes c ON b.class_id = c.id 
                WHERE 1=1 $status_condition $class_condition $date_condition $search_condition";
                
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['count'];
$total_pages = ceil($total_records / $records_per_page);

$bookings_query = "SELECT b.*, u.username, u.full_name, u.email, c.name as class_name 
                  FROM bookings b 
                  JOIN users u ON b.user_id = u.id 
                  LEFT JOIN classes c ON b.class_id = c.id 
                  WHERE 1=1 $status_condition $class_condition $date_condition $search_condition
                  ORDER BY b.booking_date DESC, b.booking_time DESC 
                  LIMIT $offset, $records_per_page";
                  
$bookings_result = mysqli_query($conn, $bookings_query);

// Get classes for filter dropdown
$classes_query = "SELECT id, name FROM classes ORDER BY name";
$classes_result = mysqli_query($conn, $classes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - FitZone</title>
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
        
        .filter-form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .filter-form .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .filter-form .form-group {
            margin-right: 15px;
            margin-bottom: 10px;
        }
        
        .filter-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .filter-form select,
        .filter-form input[type="text"],
        .filter-form input[type="date"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .filter-form button {
            padding: 8px 15px;
            background-color: #00c6ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .filter-form a {
            display: inline-block;
            padding: 8px 15px;
            background-color: #f1f1f1;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
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
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
        }
        
        .badge-pending {
            background-color: #ff9800;
        }
        
        .badge-confirmed {
            background-color: #4caf50;
        }
        
        .badge-cancelled {
            background-color: #f44336;
        }
        
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .admin-nav ul {
                flex-direction: column;
            }
            .admin-nav ul li {
                margin-bottom: 10px;
            }
            .filter-form .form-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .filter-form .form-group {
                width: 100%;
                margin-right: 0;
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
            <h2>Manage Bookings</h2>
        </div>
        
        <?php echo $message; ?>
        
        <form class="filter-form" method="GET" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="class_id">Class:</label>
                    <select name="class_id" id="class_id">
                        <option value="0">All Classes</option>
                        <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $class_filter == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo $class['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">Date From:</label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Date To:</label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>">
                </div>
                
                <div class="form-group">
                    <label for="search">Search User:</label>
                    <input type="text" name="search" id="search" placeholder="Username or email..." value="<?php echo $search; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <button type="submit">Apply Filters</button>
                    <a href="manage_bookings.php">Clear Filters</a>
                </div>
            </div>
        </form>
        
        <div class="card">
            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">User</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Class</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Time</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Created</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $booking['id']; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <strong><?php echo $booking['username']; ?></strong><br>
                                    <?php echo $booking['full_name']; ?><br>
                                    <small><?php echo $booking['email']; ?></small>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $booking['class_name'] ? $booking['class_name'] : 'Personal Training'; ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php elseif ($booking['status'] == 'confirmed'): ?>
                                        <span class="badge badge-confirmed">Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;" class="action-links">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <a href="manage_bookings.php?confirm=<?php echo $booking['id']; ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?>" 
                                           style="color: #4caf50;" onclick="return confirm('Confirm this booking?');">Confirm</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                        <a href="manage_bookings.php?cancel=<?php echo $booking['id']; ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?>" 
                                           style="color: #f44336;" onclick="return confirm('Cancel this booking?');">Cancel</a>
                                    <?php endif; ?>
                                    
                                    <?php if (isAdmin()): ?>
                                        <a href="manage_bookings.php?delete=<?php echo $booking['id']; ?><?php echo isset($_GET['page']) ? '&page='.$_GET['page'] : ''; ?>" 
                                           style="color: #ff9800;" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">Delete</a>
                                    <?php endif; ?>
                                    
                                    <!-- <a href="view_booking.php?id=<?php echo $booking['id']; ?>" style="color: #00c6ff;">View Details</a> -->
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=1<?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>">&laquo; First</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>">&lsaquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>">Next &rsaquo;</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $class_filter ? '&class_id=' . $class_filter : ''; ?><?php echo $date_from ? '&date_from=' . $date_from : ''; ?><?php echo $date_to ? '&date_to=' . $date_to : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>">Last &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No bookings found matching the current filters.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>