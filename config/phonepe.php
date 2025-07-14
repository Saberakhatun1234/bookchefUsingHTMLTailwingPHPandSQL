<?php
// PhonePe Payment Configuration
// Replace these with your actual credentials from PhonePe merchant dashboard

// Test Environment (for development)
define('PHONEPE_MERCHANT_ID', 'PGTESTPAYUAT'); // Replace with your Merchant ID
define('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'); // Replace with your Salt Key
define('PHONEPE_SALT_INDEX', '1'); // Usually 1
define('PHONEPE_REDIRECT_URL', 'http://localhost/bookchef/user/payment-callback.php'); // Your callback URL
define('PHONEPE_API_URL', 'https://api-preprod.phonepe.com/apis/pg-sandbox'); // Test URL

// Production Environment (uncomment when going live)
// define('PHONEPE_MERCHANT_ID', 'YOUR_PRODUCTION_MERCHANT_ID');
// define('PHONEPE_SALT_KEY', 'YOUR_PRODUCTION_SALT_KEY');
// define('PHONEPE_SALT_INDEX', '1');
// define('PHONEPE_REDIRECT_URL', 'https://yourdomain.com/bookchef/user/payment-callback.php');
// define('PHONEPE_API_URL', 'https://api.phonepe.com/apis/hermes');

/**
 * Generate PhonePe payment request
 */
function generatePhonePePayment($merchantTransactionId, $amount, $merchantUserId, $mobileNumber, $callbackUrl) {
    $merchantId = PHONEPE_MERCHANT_ID;
    $saltKey = PHONEPE_SALT_KEY;
    $saltIndex = PHONEPE_SALT_INDEX;
    
    // Create payload
    $payload = array(
        "merchantId" => $merchantId,
        "merchantTransactionId" => $merchantTransactionId,
        "merchantUserId" => $merchantUserId,
        "amount" => $amount * 100, // Amount in paise
        "redirectUrl" => $callbackUrl,
        "redirectMode" => "POST",
        "callbackUrl" => $callbackUrl,
        "mobileNumber" => $mobileNumber,
        "paymentInstrument" => array(
            "type" => "PAY_PAGE"
        )
    );
    
    $jsonPayload = json_encode($payload);
    $base64Payload = base64_encode($jsonPayload);
    
    // Generate checksum
    $string = $base64Payload . "/pg/v1/pay" . $saltKey;
    $sha256 = hash("sha256", $string);
    $checksum = $sha256 . "###" . $saltIndex;
    
    // Create request
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => PHONEPE_API_URL . "/pg/v1/pay",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode(array(
            "request" => $base64Payload
        )),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "X-VERIFY: " . $checksum
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return array("success" => false, "error" => "cURL Error: " . $err);
    } else {
        $responseData = json_decode($response, true);
        return $responseData;
    }
}

/**
 * Verify PhonePe payment response
 */
function verifyPhonePePayment($merchantTransactionId, $amount, $transactionId, $providerReferenceId) {
    $merchantId = PHONEPE_MERCHANT_ID;
    $saltKey = PHONEPE_SALT_KEY;
    $saltIndex = PHONEPE_SALT_INDEX;
    
    // Create verification string
    $string = "/pg/v1/status/" . $merchantId . "/" . $merchantTransactionId . $saltKey;
    $sha256 = hash("sha256", $string);
    $checksum = $sha256 . "###" . $saltIndex;
    
    // Verify payment status
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => PHONEPE_API_URL . "/pg/v1/status/" . $merchantId . "/" . $merchantTransactionId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "X-VERIFY: " . $checksum,
            "X-MERCHANT-ID: " . $merchantId
        ),
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return array("success" => false, "error" => "cURL Error: " . $err);
    } else {
        $responseData = json_decode($response, true);
        return $responseData;
    }
}
?> 