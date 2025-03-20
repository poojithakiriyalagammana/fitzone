<?php
include 'includes/header.php';

$message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $query_message = sanitize($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($query_message)) {
        $message = displayError("Please fill all required fields");
    } else {
        // Insert query
        $query = "INSERT INTO queries (user_id, name, email, subject, message) VALUES ('$user_id', '$name', '$email', '$subject', '$query_message')";
        
        if (mysqli_query($conn, $query)) {
            $message = displaySuccess("Your query has been submitted successfully. We will get back to you soon.");
        } else {
            $message = displayError("Failed to submit query: " . mysqli_error($conn));
        }
    }
}
?>

<div class="form-container">
    <h2>Contact Us</h2>
    <p>Have a question or need more information? Fill out the form below and we'll get back to you as soon as possible.</p>
    
    <?php echo $message; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo isLoggedIn() ? $_SESSION['username'] : ''; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Submit Query</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Our Location</h2>
    <p>FitZone Fitness Center<br>
    123 Fitness Street<br>
    Kurunegala, Sri Lanka</p>
    <p>Phone: +94 77 123 4567<br>
    Email: info@fitzone.com</p>
    
    <h3>Opening Hours</h3>
    <p>Monday - Friday: 6:00 AM - 10:00 PM<br>
    Saturday - Sunday: 8:00 AM - 8:00 PM</p>
</div>

<?php include 'includes/footer.php'; ?>