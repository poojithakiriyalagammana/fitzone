<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';

// Check if plan ID is provided
if (!isset($_GET['plan_id'])) {
    redirect('membership.php');
}

$plan_id = sanitize($_GET['plan_id']);

// Get plan details
$plan_query = "SELECT * FROM membership_plans WHERE id = '$plan_id'";
$plan_result = mysqli_query($conn, $plan_query);

if (mysqli_num_rows($plan_result) == 0) {
    redirect('membership.php');
}

$plan = mysqli_fetch_assoc($plan_result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = sanitize($_POST['card_number']);
    $exp_date = sanitize($_POST['exp_date']);
    $cvv = sanitize($_POST['cvv']);
    $name_on_card = sanitize($_POST['name_on_card']);
    
    // Validate inputs
    if (empty($card_number) || empty($exp_date) || empty($cvv) || empty($name_on_card)) {
        $message = displayError("Please fill all required fields");
    } else {
        // In a real application, you would process payment here
        // For demo purposes, just show success message
        $message = displaySuccess("Subscription successful! You have subscribed to the " . $plan['name'] . " plan.");
    }
}
?>

<h2>Subscribe to Membership Plan</h2>

<div class="card">
    <h3><?php echo $plan['name']; ?> Plan</h3>
    <p><?php echo $plan['description']; ?></p>
    <p><strong>Price:</strong> $<?php echo $plan['price']; ?> per month</p>
    <p><strong>Duration:</strong> <?php echo $plan['duration']; ?> days</p>
</div>

<div class="form-container">
    <h3>Payment Information</h3>
    <?php echo $message; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="name_on_card">Name on Card:</label>
            <input type="text" id="name_on_card" name="name_on_card" required>
        </div>
        <div class="form-group">
            <label for="card_number">Card Number:</label>
            <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" required>
        </div>
        <div class="form-group">
            <label for="exp_date">Expiration Date:</label>
            <input type="text" id="exp_date" name="exp_date" placeholder="MM/YY" required>
        </div>
        <div class="form-group">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" placeholder="XXX" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Subscribe Now</button>
            <a href="membership.php" class="btn" style="background-color: #6c757d;">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>