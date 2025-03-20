<?php include 'includes/header.php'; ?>

<h2>Membership Plans</h2>

<div class="card">
    <p>Choose a membership plan that suits your needs. We offer a variety of options to cater to different preferences and budgets. Join us today and start your journey towards a healthier lifestyle.</p>
</div>

<?php
// Fetch membership plans from database
$query = "SELECT * FROM membership_plans";
$result = mysqli_query($conn, $query);

// Check if any plans found
if (mysqli_num_rows($result) > 0) {
    echo '<div class="features">';
    while ($plan = mysqli_fetch_assoc($result)) {
        ?>
        <div class="feature">
            <h3><?php echo $plan['name']; ?> Plan</h3>
            <p><?php echo $plan['description']; ?></p>
            <p><strong>Price:</strong> $<?php echo $plan['price']; ?> per month</p>
            <p><strong>Duration:</strong> <?php echo $plan['duration']; ?> days</p>
            <?php if (isLoggedIn()): ?>
                <a href="subscribe.php?plan_id=<?php echo $plan['id']; ?>" class="btn">Subscribe Now</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login to Subscribe</a>
            <?php endif; ?>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo "<div class='card'><p>No membership plans found</p></div>";
}
?>

<?php include 'includes/footer.php'; ?>