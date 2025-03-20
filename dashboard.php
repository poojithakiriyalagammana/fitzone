<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get user bookings
$bookings_query = "SELECT b.*, c.name as class_name FROM bookings b 
                  LEFT JOIN classes c ON b.class_id = c.id 
                  WHERE b.user_id = '$user_id' 
                  ORDER BY b.booking_date DESC, b.booking_time DESC";
$bookings_result = mysqli_query($conn, $bookings_query);

// Get user queries
$queries_query = "SELECT * FROM queries WHERE user_id = '$user_id' ORDER BY created_at DESC";
$queries_result = mysqli_query($conn, $queries_query);
?>

<h2>My Dashboard</h2>

<div class="card">
    <h3>Welcome, <?php echo $user['username']; ?>!</h3>
    <p>Here you can manage your account, view your bookings, and check your query status.</p>
</div>

<div class="card">
    <h3>My Profile</h3>
    <p><strong>Full Name:</strong> <?php echo $user['full_name']; ?></p>
    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
    <p><strong>Phone:</strong> <?php echo $user['phone'] ? $user['phone'] : 'Not provided'; ?></p>
    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
    <a href="edit_profile.php" class="btn">Edit Profile</a>
</div>

<div class="card">
    <h3>My Bookings</h3>
    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Class</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Time</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $booking['class_name']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst($booking['status']); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <?php if ($booking['status'] != 'cancelled'): ?>
                                <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn" style="background-color: #dc3545; padding: 5px 10px; font-size: 14px;">Cancel</a>
                            <?php else: ?>
                                <span>Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>
    <a href="classes.php" class="btn">Book a Class</a>
</div>

<div class="card">
    <h3>My Queries</h3>
    <?php if (mysqli_num_rows($queries_result) > 0): ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Subject</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query = mysqli_fetch_assoc($queries_result)): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $query['subject']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('F j, Y', strtotime($query['created_at'])); ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst($query['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no queries yet.</p>
    <?php endif; ?>
    <a href="query.php" class="btn">Submit a Query</a>
</div>

<?php include 'includes/footer.php'; ?>