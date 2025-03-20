<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';

// Check if class ID is provided
if (!isset($_GET['class_id'])) {
    redirect('classes.php');
}

$class_id = sanitize($_GET['class_id']);

// Get class details
$class_query = "SELECT * FROM classes WHERE id = '$class_id'";
$class_result = mysqli_query($conn, $class_query);

if (mysqli_num_rows($class_result) == 0) {
    redirect('classes.php');
}

$class = mysqli_fetch_assoc($class_result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_date = sanitize($_POST['booking_date']);
    $booking_time = sanitize($_POST['booking_time']);
    
    // Validate inputs
    if (empty($booking_date) || empty($booking_time)) {
        $message = displayError("Please fill all required fields");
    } else {
        // Insert booking
        $query = "INSERT INTO bookings (user_id, class_id, booking_date, booking_time) 
                  VALUES ('$user_id', '$class_id', '$booking_date', '$booking_time')";
        
        if (mysqli_query($conn, $query)) {
            $message = displaySuccess("Class booked successfully!");
        } else {
            $message = displayError("Failed to book class: " . mysqli_error($conn));
        }
    }
}
?>

<h2>Book a Class</h2>

<div class="card">
    <h3><?php echo $class['name']; ?></h3>
    <p><?php echo $class['description']; ?></p>
    <p><strong>Schedule:</strong> <?php echo $class['schedule']; ?></p>
    <p><strong>Trainer:</strong> <?php echo $class['trainer']; ?></p>
</div>

<div class="form-container">
    <h3>Book Your Slot</h3>
    <?php echo $message; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="booking_date">Date:</label>
            <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label for="booking_time">Time:</label>
            <input type="time" id="booking_time" name="booking_time" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Book Now</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>