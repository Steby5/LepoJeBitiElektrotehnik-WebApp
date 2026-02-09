<?php
session_start();


// Function to check if user is logged in
function require_login()
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
}

// Function to verify password
function verify_admin_password($password)
{
    require_once 'server_data.php';
    return $password === ADMIN_PASSWORD;
}