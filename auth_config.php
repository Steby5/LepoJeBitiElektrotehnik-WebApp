<?php
session_start();
require_once 'server_data.php';

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
    return trim($password) === ADMIN_PASSWORD;
}