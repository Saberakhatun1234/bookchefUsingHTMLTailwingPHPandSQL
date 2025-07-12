<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$chefs = $conn->query("SELECT * FROM chefs");

if (isset($_GET['delete'])) {
    $cid = intval($_GET['delete']);
    $conn->query("DELETE FROM chefs WHERE id = $cid");
    header("Location: chefs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Chefs - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <header class="bg-green-600 text-white p-4 flex justify-between">
    <h1 class="text-xl font-bold">Manage Chefs</h1>
    <a href="dashboard.php" class="bg-gray-800 px-4 py-2 rounded">Back</a>
  </header>

  <div class="p-6">
    <a href="add-chef.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-4 inline-block">Add Chef</a>

    <table class="w-full bg-white shadow rounded table-auto">
      <thead class="bg-gray-200">
        <tr>
          <th class="p-2">Name</th>
          <th>Specialty</th>
          <th>Experience</th>
          <th>Fees</th>
          <th>Picture</th>
          <th>Availability</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($chef = $chefs->fetch_assoc()): ?>
          <tr class="text-center border-b">
            <td class="p-2"><?= htmlspecialchars($chef['name']) ?></td>
            <td><?= htmlspecialchars($chef['specialty']) ?></td>
            <td><?= htmlspecialchars($chef['experience']) ?></td>
            <td>₹<?= $chef['fees'] ?></td>
            <td>
              <img src="../assets/uploads/<?= htmlspecialchars($chef['picture']) ?>" class="w-16 h-16 object-cover rounded mx-auto">
            </td>
            <td>
              <?= $chef['is_available'] ? '✅ Available' : '❌ Unavailable' ?><br>
              <a href="toggle-chef.php?id=<?= $chef['id'] ?>" class="text-blue-600 hover:underline text-sm">
                Toggle
              </a>
            </td>
            <td>
              <a href="?delete=<?= $chef['id'] ?>" onclick="return confirm('Delete this chef?')" class="text-red-600 hover:underline">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
