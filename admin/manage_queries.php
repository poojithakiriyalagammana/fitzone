<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin or staff
if (!isStaff()) {
    redirect('../login.php');
}

$message = '';

// Handle marking query as answered
if(isset($_GET['mark_answered'])) {
    $query_id = (int)$_GET['mark_answered'];
    $update_query = "UPDATE queries SET status = 'answered' WHERE id = $query_id";
    
    if(mysqli_query($conn, $update_query)) {
        $message = displaySuccess("Query marked as answered.");
    } else {
        $message = displayError("Failed to update query: " . mysqli_error($conn));
    }
}

// Handle delete query
if(isset($_GET['delete'])) {
    $query_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM queries WHERE id = $query_id";
    
    if(mysqli_query($conn, $delete_query)) {
        $message = displaySuccess("Query deleted successfully.");
    } else {
        $message = displayError("Failed to delete query: " . mysqli_error($conn));
    }
}

// Get queries with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$status_condition = $status_filter ? "WHERE status = '$status_filter'" : "";

$count_query = "SELECT COUNT(*) as count FROM queries $status_condition";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['count'];
$total_pages = ceil($total_records / $records_per_page);

$queries_query = "SELECT q.*, u.username 
                 FROM queries q 
                 LEFT JOIN users u ON q.user_id = u.id 
                 $status_condition 
                 ORDER BY q.created_at DESC 
                 LIMIT $offset, $records_per_page";
$queries_result = mysqli_query($conn, $queries_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Queries - FitZone</title>
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
        }
        
        .filter-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-form button {
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
        
        .badge-answered {
            background-color: #4caf50;
        }
        
        @media (max-width: 768px) {
            .admin-nav ul {
                flex-direction: column;
            }
            .admin-nav ul li {
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
            <h2>Manage Queries</h2>
        </div>
        
        <?php echo $message; ?>
        
        <form class="filter-form" method="GET" action="">
            <select name="status">
                <option value="">All Queries</option>
                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="answered" <?php echo $status_filter == 'answered' ? 'selected' : ''; ?>>Answered</option>
            </select>
            <button type="submit">Filter</button>
            <?php if($status_filter): ?>
                <a href="manage_queries.php" style="margin-left: 10px;">Clear</a>
            <?php endif; ?>
        </form>
        
        <div class="card">
            <?php if (mysqli_num_rows($queries_result) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align:left;">Email</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Subject</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($query = mysqli_fetch_assoc($queries_result)): ?>
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['id']; ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['name']; ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['email']; ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['subject']; ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('M j, Y', strtotime($query['created_at'])); ?></td>
                <td style="border: 1px solid #ddd; padding: 8px;">
                    <span class="badge badge-<?php echo $query['status']; ?>">
                        <?php echo ucfirst($query['status']); ?>
                    </span>
                </td>
                <td style="border: 1px solid #ddd; padding: 8px;">
                    <!-- <a href="view_query.php?id=<?php echo $query['id']; ?>" style="margin-right: 10px;">View</a> -->
                    <?php if($query['status'] == 'pending'): ?>
                        <a href="manage_queries.php?mark_answered=<?php echo $query['id']; ?>" style="margin-right: 10px;">Mark Answered</a>
                    <?php endif; ?>
                    <a href="manage_queries.php?delete=<?php echo $query['id']; ?>" onclick="return confirm('Are you sure you want to delete this query?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if($page > 1): ?>
        <a href="?page=1<?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">&laquo; First</a>
        <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">&lsaquo; Prev</a>
    <?php endif; ?>
    
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);
    
    for($i = $start_page; $i <= $end_page; $i++):
    ?>
        <?php if($i == $page): ?>
            <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Next &rsaquo;</a>
        <a href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Last &raquo;</a>
    <?php endif; ?>
</div>
<?php else: ?>
    <p>No queries found.</p>
<?php endif; ?>
</div>
</div>

<script src="../js/script.js"></script>
</body>
</html>