<?php
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$user_id = $_SESSION['user_id'];

// Get user data
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name) || empty($email)) {
        $message = displayError("Name and email are required");
    } else {
        // Check if email already exists (if changed)
        if ($email != $user['email']) {
            $check_query = "SELECT * FROM users WHERE email = '$email' AND id != '$user_id'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $message = displayError("Email already exists");
            }
        }
        
        // If no errors, update profile
        if (empty($message)) {
            // If password change is requested
            if (!empty($current_password) && !empty($new_password)) {
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    $message = displayError("Current password is incorrect");
                } elseif ($new_password != $confirm_password) {
                    $message = displayError("New passwords do not match");
                } else {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update user with new password
                    $update_query = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone', password = '$hashed_password' WHERE id = '$user_id'";
                }
            } else {
                // Update user without changing password
                $update_query = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' WHERE id = '$user_id'";
            }
            
            // Execute update query
            if (mysqli_query($conn, $update_query)) {
                $message = displaySuccess("Profile updated successfully!");
                
                // Refresh user data
                $result = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
                $user = mysqli_fetch_assoc($result);
            } else {
                $message = displayError("Failed to update profile: " . mysqli_error($conn));
            }
        }
    }
}
?>

<h2>Edit Profile</h2>

<div class="form-container">
    <h3>Update Your Information</h3>
    <?php echo $message; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled>
            <p class="form-help">Username cannot be changed</p>
        </div>
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
        </div>
        
        <h4>Change Password (leave blank to keep current password)</h4>
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Update Profile</button>
            <a href="dashboard.php" class="btn" style="background-color: #6c757d;">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>