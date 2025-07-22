<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) 
    exit;

// admin_auth.php
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conn.php'; // Ensure this path is correct relative to where admin_auth.php is

include_once __DIR__ . 'conn.php'; 
?>
