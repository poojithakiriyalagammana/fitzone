<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Function to check if user is staff
function isStaff() {
    return isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 'staff' || $_SESSION['user_type'] == 'admin');
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Function to redirect
function redirect($location) {
    header("Location: $location");
    exit;
}

// Function to display error message
function displayError($message) {
    return "<div class='error'>$message</div>";
}

// Function to display success message
function displaySuccess($message) {
    return "<div class='success'>$message</div>";
}
?>