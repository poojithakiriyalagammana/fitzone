<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$booking_id = sanitize($_GET['id']);
$user_id = $_SESSION['user_id'];

// Check if booking belongs to the user
$query = "SELECT * FROM bookings WHERE id = '$booking_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('dashboard.php');
}

$booking = mysqli_fetch_assoc($result);

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update booking status
    $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = '$booking_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $message = displaySuccess("Booking cancelled successfully!");
    } else {
        $message = displayError("Failed to cancel booking: " . mysqli_error($conn));
    }
}

// Get class details
$class_query = "SELECT name FROM classes WHERE id = '{$booking['class_id']}'";
$class_result = mysqli_query($conn, $class_query);
$class = mysqli_fetch_assoc($class_result);
?>

<h2>Cancel Booking</h2>

<div class="form-container">
    <?php echo $message; ?>
    
    <?php if ($booking['status'] != 'cancelled'): ?>
        <h3>Are you sure you want to cancel this booking?</h3>
        <p><strong>Class:</strong> <?php echo $class['name']; ?></p>
        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
        
        <form method="POST" action="">
            <div class="form-group">
                <button type="submit" class="btn" style="background-color: #dc3545;">Yes, Cancel Booking</button>
                <a href="dashboard.php" class="btn" style="background-color: #6c757d;">No, Go Back</a>
            </div>
        </form>
    <?php else: ?>
        <p>This booking has already been cancelled.</p>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>