<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$bookingId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

// Only cancel if it belongs to the logged-in user
$conn->query("UPDATE bookings SET status = 'canceled' WHERE id = $bookingId AND user_id = $userId");

header("Location: profile.php");
exit();
