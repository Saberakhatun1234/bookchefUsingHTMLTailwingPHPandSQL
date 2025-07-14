<?php
session_start();
include '../config/db.php';
include '../config/phonepe.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get payment response from PhonePe
$merchantTransactionId = $_POST['merchantTransactionId'] ?? '';
$transactionId = $_POST['transactionId'] ?? '';
$amount = $_POST['amount'] ?? '';
$providerReferenceId = $_POST['providerReferenceId'] ?? '';
$code = $_POST['code'] ?? '';
$paymentInstrument = $_POST['paymentInstrument'] ?? '';

// Verify payment status
$verificationResponse = verifyPhonePePayment($merchantTransactionId, $amount, $transactionId, $providerReferenceId);

if (isset($verificationResponse['success']) && $verificationResponse['success']) {
    $paymentStatus = $verificationResponse['data']['paymentState'];
    
    if ($paymentStatus === 'COMPLETED') {
        // Payment successful - create or update booking
        if (isset($_SESSION['payment_details'])) {
            $paymentDetails = $_SESSION['payment_details'];
            
            // Check if booking already exists
            $existingBooking = $conn->query("SELECT * FROM bookings WHERE user_id = {$paymentDetails['user_id']} AND chef_id = {$paymentDetails['chef_id']} AND payment_status = 'pending'");
            
            if ($existingBooking->num_rows > 0) {
                // Update existing booking
                $booking = $existingBooking->fetch_assoc();
                $updateSql = "UPDATE bookings SET payment_status = 'paid', payment_id = ? WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("si", $transactionId, $booking['id']);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Payment successful! Your booking is confirmed.";
                    unset($_SESSION['payment_details']);
                    header("Location: profile.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Payment successful but booking update failed. Please contact support.";
                }
            } else {
                // Create new booking
                $insertSql = "INSERT INTO bookings (user_id, chef_id, fees, payment_status, payment_id, created_at) VALUES (?, ?, ?, 'paid', ?, NOW())";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("iids", $paymentDetails['user_id'], $paymentDetails['chef_id'], $paymentDetails['fees'], $transactionId);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Payment successful! Your booking is confirmed.";
                    unset($_SESSION['payment_details']);
                    header("Location: profile.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Payment successful but booking creation failed. Please contact support.";
                }
            }
        } else {
            $_SESSION['error'] = "Payment successful but session data missing. Please contact support.";
        }
    } else {
        $_SESSION['error'] = "Payment failed or pending. Please try again.";
    }
} else {
    $_SESSION['error'] = "Payment verification failed. Please contact support.";
}

// Redirect back to profile page
header("Location: profile.php");
exit();
?> 