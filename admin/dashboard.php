<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <header class="bg-black text-white p-4 flex justify-between">
    <h1 class="text-xl font-bold">Admin Dashboard</h1>
    <a href="../auth/logout.php" class="bg-red-500 px-4 py-2 rounded">Logout</a>
  </header>

  <main class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
    <a href="users.php" class="bg-blue-600 text-white p-6 rounded-lg text-center hover:bg-blue-700">
      👤 Manage Users
    </a>
    <a href="chefs.php" class="bg-green-600 text-white p-6 rounded-lg text-center hover:bg-green-700">
      🍽️ Manage Chefs
    </a>
    <a href="booking.php" class="bg-purple-600 text-white p-6 rounded-lg text-center hover:bg-purple-700">
      📅 View Chef Bookings
    </a>
  </main>

</body>
</html>
