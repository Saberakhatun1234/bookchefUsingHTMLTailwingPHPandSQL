<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['chef_id'])) {
    echo "Invalid request.";
    exit();
}

$chef_id = $_GET['chef_id'];

// Get user & chef data
$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$chef_result = $conn->query("SELECT * FROM chefs WHERE id = $chef_id");

if ($user_result->num_rows !== 1 || $chef_result->num_rows !== 1) {
    echo "Data not found.";
    exit();
}

$user = $user_result->fetch_assoc();
$chef = $chef_result->fetch_assoc();

// Handle "Book Now" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    $event_datetime = $_POST['event_datetime'];
    $event_place = $_POST['event_place'];
    $phone = $_POST['phone'];
    
    // Parse datetime
    $datetime = new DateTime($event_datetime);
    $now = new DateTime();
    if ($datetime < $now) {
        $error = "You cannot book a chef for a past date/time.";
    } else {
        $event_date = $datetime->format('Y-m-d');
        $event_time = $datetime->format('H:i:s');

        // Check if chef is already booked for this date and time slot
        $check_sql = "SELECT * FROM bookings WHERE chef_id = ? AND event_date = ? AND event_time = ? AND payment_status IN ('pending', 'paid')";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iss", $chef_id, $event_date, $event_time);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $error = "Sorry, this chef is already booked for the selected date and time.";
        } else {
            // Insert booking with pending payment status
            $sql = "INSERT INTO bookings (user_id, chef_id, event_date, event_time, event_place, phone, fees, payment_status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssd", $user_id, $chef_id, $event_date, $event_time, $event_place, $phone, $chef['fees']);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Booking created successfully! You can pay later from your profile.";
                header("Location: profile.php");
                exit();
            } else {
                $error = "Error creating booking. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Chef - <?= $chef['name'] ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded shadow-md w-full max-w-xl">
    <h2 class="text-2xl font-bold mb-4 text-center text-blue-600">Booking: <?= $chef['name'] ?></h2>
    
    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="payment.php">
      <input type="hidden" name="chef_id" value="<?= $chef['id'] ?>">
      <input type="hidden" name="fees" value="<?= $chef['fees'] ?>">

      <label class="block mb-1 font-medium">Event Date & Time</label>
      <input type="datetime-local" name="event_datetime" required class="w-full mb-4 p-2 border rounded" min="<?= date('Y-m-d\TH:i') ?>">

      <label class="block mb-1 font-medium">Event Place</label>
      <input type="text" name="event_place" placeholder="e.g. Purulia, West Bengal" required class="w-full mb-4 p-2 border rounded">

      <label class="block mb-1 font-medium">Phone Number</label>
      <input type="text" name="phone" value="<?= $user['phone'] ?>" required class="w-full mb-4 p-2 border rounded">

      <div class="flex gap-4 justify-center">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Pay and Book</button>
      </div>
    </form>
    
  </div>

</body>
</html>
