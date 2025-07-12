<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$users = $conn->query("SELECT * FROM users WHERE is_admin = 0");

if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $uid");
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <header class="bg-blue-600 text-white p-4 flex justify-between">
    <h1 class="text-xl font-bold">Manage Users</h1>
    <a href="dashboard.php" class="bg-gray-800 px-4 py-2 rounded">Back</a>
  </header>

  <div class="p-6">
    <table class="w-full bg-white shadow rounded table-auto">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2">Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = $users->fetch_assoc()): ?>
          <tr class="text-center border-b">
            <td class="p-2"><?= $user['name'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['phone'] ?></td>
            <td>
              <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Delete user?')" class="text-red-600 hover:underline">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
