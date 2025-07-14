<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$result = $conn->query("SELECT b.*, u.name AS user_name, c.name AS chef_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN chefs c ON b.chef_id = c.id
    ORDER BY b.event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Bookings - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-green-700 text-white p-4 flex justify-between">
    <h1 class="text-xl font-bold">All Bookings</h1>
    <a href="dashboard.php" class="bg-gray-800 px-4 py-2 rounded">Back</a>
  </header>

  <div class="p-6">
    <table class="w-full bg-white shadow rounded table-auto">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2">User</th>
          <th>Chef</th>
          <th>Date</th>
          <th>Time</th>
          <th>Place</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $event_datetime = $row['event_date'] . ' ' . $row['event_time'];
            $now = date('Y-m-d H:i:s');
            $is_completed = ($event_datetime < $now);
          ?>
          <tr class="text-center border-b">
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['chef_name']) ?></td>
            <td><?= $row['event_date'] ?></td>
            <td><?= $row['event_time'] ?></td>
            <td><?= htmlspecialchars($row['event_place']) ?></td>
            <td class="<?= $is_completed ? 'text-gray-500' : ($row['status'] === 'canceled' ? 'text-red-600' : 'text-green-700') ?>">
              <?= $is_completed ? 'Completed' : ucfirst($row['status']) ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
