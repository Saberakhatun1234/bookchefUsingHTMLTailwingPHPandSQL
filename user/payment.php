<?php
session_start();
include '../config/db.php';
include '../config/phonepe.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_POST['chef_id']) || !isset($_POST['fees'])) {
    header("Location: index.php");
    exit();
}

$chef_id = $_POST['chef_id'];
$fees = $_POST['fees'];
$user_id = $_SESSION['user_id'];

// Get user and chef details
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$chef_result = $conn->query("SELECT * FROM chefs WHERE id = $chef_id");

if ($user_result->num_rows !== 1 || $chef_result->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$user = $user_result->fetch_assoc();
$chef = $chef_result->fetch_assoc();

// Generate unique transaction ID
$merchantTransactionId = 'TXN_' . time() . '_' . $user_id . '_' . $chef_id;

// Create PhonePe payment request
$callbackUrl = PHONEPE_REDIRECT_URL;
$paymentResponse = generatePhonePePayment(
    $merchantTransactionId,
    $fees,
    $user_id,
    $user['phone'],
    $callbackUrl
);

if (isset($paymentResponse['success']) && $paymentResponse['success']) {
    // Store payment details in session for callback
    $_SESSION['payment_details'] = array(
        'merchantTransactionId' => $merchantTransactionId,
        'chef_id' => $chef_id,
        'fees' => $fees,
        'user_id' => $user_id
    );
    
    // Redirect to PhonePe payment page
    $paymentUrl = $paymentResponse['data']['instrumentResponse']['redirectInfo']['url'];
    header("Location: " . $paymentUrl);
    exit();
} else {
    $error = "Payment initialization failed. Please try again.";
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
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-center text-red-600">Payment Error</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
