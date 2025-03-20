<?php include 'includes/header.php'; ?>

<h2>Our Classes</h2>

<div class="card">
    <p>At FitZone Fitness Center, we offer a variety of classes to help you achieve your fitness goals. Whether you're looking to improve your cardio, build strength, increase flexibility, or find mental balance, we have a class for you.</p>
</div>

<?php
// Fetch classes from database
$query = "SELECT * FROM classes";
$result = mysqli_query($conn, $query);

// Check if any classes found
if (mysqli_num_rows($result) > 0) {
    while ($class = mysqli_fetch_assoc($result)) {
        ?>
        <div class="card">
            <h3><?php echo $class['name']; ?></h3>
            <p><?php echo $class['description']; ?></p>
            <p><strong>Schedule:</strong> <?php echo $class['schedule']; ?></p>
            <p><strong>Trainer:</strong> <?php echo $class['trainer']; ?></p>
            <?php if (isLoggedIn()): ?>
                <a href="book_class.php?class_id=<?php echo $class['id']; ?>" class="btn">Book Now</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login to Book</a>
            <?php endif; ?>
        </div>
        <?php
    }
} else {
    echo "<div class='card'><p>No classes found</p></div>";
}
?>

<?php include 'includes/footer.php'; ?>