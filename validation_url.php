<?php
header("Content-Type: application/x-www-form-urlencoded");
$response = '{ "ResultCode": 0, "ResultDesc": "Confirmation Received Successfully" }';
$mpesaResponse = file_get_contents('php://input');
$logFile = "C2bValidationData.txt";
$jsonMpesaResponse = json_decode($mpesaResponse, true);
$log = fopen($logFile, "a");
fwrite($log, $mpesaResponse);
fclose($log); 