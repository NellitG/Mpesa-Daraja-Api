<?php
// Include the access token file
include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');

// Process request URL for STK Push
$processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

// Callback URL - This should match your Ngrok URL or production URL
$callbackurl = 'https://1c95-105-161-14-223.ngrok-free.app/MPEsa-Daraja-Api/callback.php';

// Business credentials
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$BusinessShortCode = '174379';
$Timestamp = date('YmdHis');

// Encrypt password using the formula
$Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

// Get mobile number and donation amount from the POST request
$mobileNumber = isset($_POST['mobileNumber']) ? $_POST['mobileNumber'] : null;
$donationAmount = isset($_POST['donationAmount']) ? $_POST['donationAmount'] : null;

// Validate the input
if (empty($mobileNumber) || empty($donationAmount)) {
    echo json_encode(['error' => 'Mobile number and donation amount are required.']);
    exit;
}

// Other required parameters for the STK Push request
$PartyA = $mobileNumber;  // Mobile number of the donor
$PartyB = $BusinessShortCode;
$AccountReference = 'NYAYO INC FOUNDATION';
$TransactionDesc = 'Donation Payment';
$Amount = $donationAmount; // Donation amount from the frontend

// STK Push request headers
$stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

// Initialize cURL session for the STK Push request
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); // Set the custom headers

// Prepare the STK Push request data
$curl_post_data = array(
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $Amount,
    'PartyA' => $PartyA,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $PartyA,
    'CallBackURL' => $callbackurl,
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
);

// Convert the request data to JSON format
$data_string = json_encode($curl_post_data);

// Execute the cURL request
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

// Get the response from Safaricom
$curl_response = curl_exec($curl);

// Decode the JSON response
$data = json_decode($curl_response);

// Get the CheckoutRequestID and ResponseCode from the response
$CheckoutRequestID = isset($data->CheckoutRequestID) ? $data->CheckoutRequestID : null;
$ResponseCode = isset($data->ResponseCode) ? $data->ResponseCode : null;

// Check if the STK Push was successful
if ($ResponseCode == "0") {
    // Response is successful, return CheckoutRequestID
    echo json_encode([
        'message' => 'STK Push initiated successfully.',
        'CheckoutRequestID' => $CheckoutRequestID
    ]);
} else {
    // Response failed, return the error message
    echo json_encode(['error' => 'Failed to initiate STK Push. Please try again later.']);
}

// Close the cURL session
curl_close($curl);
?>
