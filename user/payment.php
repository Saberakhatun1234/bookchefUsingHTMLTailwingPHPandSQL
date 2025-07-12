<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Handle payment for existing pending booking
if (isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    
    // Get booking details
    $booking_result = $conn->query("SELECT * FROM bookings WHERE id = $booking_id AND user_id = $user_id AND payment_status = 'pending'");
    
    if ($booking_result->num_rows === 1) {
        $booking = $booking_result->fetch_assoc();
        
        if (isset($_POST['confirm_booking'])) {
            // Update booking to paid status
            $payment_id = uniqid("PAYID_");
            $update_sql = "UPDATE bookings SET payment_status = 'confirmed', payment_id = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sii", $payment_id, $booking_id, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Payment successful! Your booking is confirmed.";
                header("Location: profile.php");
                exit();
            } else {
                $error = "Payment failed. Please try again.";
            }
        }
        
        // Display payment form for existing booking
        $user_name = $user['name']; // Get from users table instead of bookings
        $fees = $booking['fees'];
        $event_date = $booking['event_date'];
        $event_time = $booking['event_time'];
        $event_place = $booking['event_place'];
        $phone = $booking['phone'];
    } else {
        header("Location: profile.php");
        exit();
    }
} else {
    // Handle new booking payment
    $user_name = $user['name'];
    $chef_id = $_POST['chef_id'];
    $event_datetime = $_POST['event_datetime'];
    $event_place = $_POST['event_place'];
    $phone = $_POST['phone'];
    $fees = $_POST['fees'];

    // Split datetime into date and time
    $dt = new DateTime($event_datetime);
    $event_date = $dt->format('Y-m-d');
    $event_time = $dt->format('H:i');

    if (isset($_POST['confirm_booking'])) {
        $stmt = $conn->prepare("
            INSERT INTO bookings 
            (user_id, chef_id, event_date, event_time, event_place, phone, fees, status, payment_status, payment_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 'confirmed', ?, NOW())
        ");
        
        // Simulated payment ID (in real case, this comes from Razorpay or PhonePe)
        $payment_id = uniqid("PAYID_");

        $stmt->bind_param("iissssis", $user_id, $chef_id, $event_date, $event_time, $event_place, $phone, $fees, $payment_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Booking successful and payment received!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Booking failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded shadow-md w-full max-w-lg text-center">
    <h2 class="text-2xl font-bold text-blue-600 mb-4">Confirm & Pay</h2>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <p class="text-lg mb-2">Booking for: <strong><?= htmlspecialchars($user_name) ?></strong></p>
    <p class="text-gray-700 mb-4">Chef Fees: <span class="font-semibold text-green-600">₹<?= $fees ?></span></p>

    <div class="mb-6">
    
      <img src="../assets/qr-sample.jpeg" alt="Scan to Pay" class="mx-auto size-48 border">
      <p class="text-gray-500 mt-2 text-sm">Scan the QR code to pay ₹<?= $fees ?></p>
    </div>

    <form method="POST">
      <?php if (isset($_POST['booking_id'])): ?>
        <!-- Hidden Fields for existing booking -->
        <input type="hidden" name="booking_id" value="<?= $_POST['booking_id'] ?>">
      <?php else: ?>
        <!-- Hidden Fields for new booking -->
        <input type="hidden" name="chef_id" value="<?= $chef_id ?>">
        <input type="hidden" name="user_name" value="<?= htmlspecialchars($user_name) ?>">
        <input type="hidden" name="event_datetime" value="<?= $event_datetime ?>">
        <input type="hidden" name="event_place" value="<?= htmlspecialchars($event_place) ?>">
        <input type="hidden" name="phone" value="<?= $phone ?>">
        <input type="hidden" name="fees" value="<?= $fees ?>">
      <?php endif; ?>
      
      <input type="hidden" name="confirm_booking" value="1">

      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
        Pay & Book
      </button>
      
    </form>
  </div>

</body>
</html>
