<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cancel booking (only if still unpaid)
if (isset($_GET['cancel_booking'])) {
    $booking_id = intval($_GET['cancel_booking']);
    // Ensure payment is still pending before delete
    $conn->query("DELETE FROM bookings WHERE id = $booking_id AND user_id = $user_id AND payment_status = 'pending'");
    $_SESSION['success'] = "Booking cancelled successfully.";
    header("Location: profile.php");
    exit();
}

// Get user info
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get booking history
$bookings = $conn->query("
    SELECT b.*, c.name AS chef_name, c.fees 
    FROM bookings b
    JOIN chefs c ON b.chef_id = c.id
    WHERE b.user_id = $user_id
    ORDER BY b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Profile - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
    <h1 class="text-xl font-semibold">Your Profile</h1>
    <div class="flex gap-2">
      <a href="edit-profile.php" class="bg-yellow-500 px-4 py-2 rounded hover:bg-yellow-600">Edit Profile</a>
      <a href="index.php" class="bg-gray-700 px-4 py-2 rounded hover:bg-gray-800">Back to Dashboard</a>
    </div>
  </header>

  <div class="max-w-5xl mx-auto p-6">

    <!-- Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <!-- Profile Info -->
    <div class="bg-white rounded shadow-md p-6 mb-6 flex items-center gap-6">
      <img src="../assets/uploads/<?= htmlspecialchars($user['profile_pic']) ?>" class="w-24 h-24 rounded-full border object-cover" alt="Profile Picture">
      <div>
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h2>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Phone: <?= htmlspecialchars($user['phone']) ?></p>
        <p>Address: <?= htmlspecialchars($user['address']) ?></p>
      </div>
    </div>

    <!-- Booking History -->
    <h3 class="text-xl font-semibold mb-3">Booking History</h3>

    <?php if ($bookings->num_rows > 0): ?>
      <div class="grid gap-4">
        <?php while ($row = $bookings->fetch_assoc()): ?>
          <div class="bg-white p-4 rounded shadow-md flex justify-between items-center">
            <div>
              <p class="text-lg font-bold"><?= htmlspecialchars($row['chef_name']) ?> (₹<?= $row['fees'] ?>)</p>
              <p class="text-gray-700">
                Event: <?= htmlspecialchars($row['event_date']) ?>
                <?php if (!empty($row['event_time'])): ?>
                  at <?= htmlspecialchars($row['event_time']) ?>
                <?php endif; ?>
                , <?= htmlspecialchars($row['event_place']) ?>
              </p>
              <p class="text-gray-600 text-sm">Phone: <?= htmlspecialchars($row['phone']) ?></p>

              <!-- Payment Status -->
              <p class="text-sm mt-1">
                Payment:
                <?php if ($row['payment_status'] === 'pending'): ?>
                  <span class="text-yellow-600 font-medium">Pending</span>
                  <form method="POST" action="payment.php" style="display: inline;">
                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="ml-2 inline-block bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">
                      Pay Now
                    </button>
                  </form>
                <?php elseif ($row['payment_status'] === 'confirmed'): ?>
                  <span class="text-green-600 font-medium">Confirmed</span>
                <?php endif; ?>
              </p>
            </div>

            <!-- Cancel Option if Payment is Still Pending -->
            <?php if ($row['payment_status'] === 'pending'): ?>
              <a href="?cancel_booking=<?= $row['id'] ?>" 
                 onclick="return confirm('Are you sure you want to cancel this booking?')"
                 class="text-red-600 hover:underline font-medium">Cancel</a>
            <?php else: ?>
              <span class="text-gray-500 italic">Paid</span>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600">No bookings yet.</p>
    <?php endif; ?>

  </div>

</body>
</html>
