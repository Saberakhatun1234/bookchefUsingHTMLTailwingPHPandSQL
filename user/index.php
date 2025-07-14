<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/db.php';

$userName = $_SESSION['user_name'];
$isAdmin = $_SESSION['is_admin'] ?? 0;

$result = $conn->query("SELECT * FROM chefs");
?>

<?php
// Fetch all bookings for chefs
$bookings = [];
$now = date('Y-m-d H:i:s');
$booking_result = $conn->query("SELECT * FROM bookings WHERE payment_status IN ('pending', 'paid')");
while ($row = $booking_result->fetch_assoc()) {
    // Only consider future bookings for disabling
    $event_datetime = $row['event_date'] . ' ' . $row['event_time'];
    if ($event_datetime > $now) {
        $bookings[$row['chef_id']][$row['user_id']] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Header -->
  <header class="bg-blue-600 text-white p-4 flex flex-wrap gap-4 justify-around items-center">
    <h1 class="text-xl font-semibold">Welcome, <?= htmlspecialchars($userName) ?> 👋</h1>

    <a href="profile.php" class="bg-green-500 px-4 py-2 rounded hover:bg-green-600">Profile</a>

    <?php if ($isAdmin): ?>
      <a href="../admin/dashboard.php" class="bg-yellow-500 px-4 py-2 rounded hover:bg-yellow-600">Admin</a>
    <?php endif; ?>

    <a href="../auth/logout.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">Logout</a>
  </header>

  <!-- Chef Cards -->
  <main class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    <?php while ($chef = $result->fetch_assoc()): ?>
      <div class="bg-white rounded shadow-md p-4 relative">
        <img src="../assets/uploads/<?= htmlspecialchars($chef['picture']) ?>" alt="Chef" class="w-full h-48 object-cover rounded">

        <h2 class="text-xl font-bold mt-2"><?= htmlspecialchars($chef['name']) ?></h2>
        <p class="text-gray-700">Specialty: <?= htmlspecialchars($chef['specialty']) ?></p>
        <p class="text-gray-700">Experience: <?= htmlspecialchars($chef['experience']) ?></p>
        <p class="text-gray-900 font-semibold">Fees: ₹<?= $chef['fees'] ?></p>

        <!-- Availability Badge -->
        <p class="text-sm mt-2">
          Availability:
          <span class="<?= $chef['is_available'] == 1 ? 'text-green-600' : 'text-red-500' ?> font-medium">
            <?= $chef['is_available'] == 1 ? 'Available' : 'Unavailable' ?>
          </span>
        </p>

        <!-- Booking Status -->
        <?php if (isset($bookings[$chef['id']][$_SESSION['user_id']])): ?>
          <button disabled class="mt-3 inline-block bg-yellow-500 text-white px-4 py-2 rounded cursor-not-allowed">Booked by you</button>
        <?php elseif ($chef['is_available'] == 1): ?>
          <a href="book-chef.php?chef_id=<?= $chef['id'] ?>" 
             class="mt-3 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Book Now
          </a>
        <?php else: ?>
          <button disabled 
                  class="mt-3 inline-block bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed">
            Unavailable
          </button>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </main>

</body>
</html>
