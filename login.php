<?php
// login.php

session_start();

$correct_username = 'user';  // Set your correct username
$correct_password = 'root';  // Set your correct password

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username and password are correct
    if ($username === $correct_username && $password === $correct_password) {
        $_SESSION['loggedin'] = true;
        header("Location: welcome.php");  // Redirect to a new page if login is successful
        exit();
    } else {
        echo "<script>alert('Invalid Username or Password!'); window.location.href = 'index.html';</script>";  // Show an error message if login fails
    }
}
?>
