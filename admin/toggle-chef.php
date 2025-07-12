<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id']);
$conn->query("UPDATE chefs SET is_available = IF(is_available = 1, 0, 1) WHERE id = $id");

header("Location: chefs.php");
exit();
