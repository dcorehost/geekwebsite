<?php
require 'db.php'; // Include the MongoDB connection
session_start();

// Add the necessary namespace for MongoDB BSON
use MongoDB\BSON\UTCDateTime;

// CORS headers to allow requests from different origins
header('Access-Control-Allow-Origin: *');  // Allow all origins (or replace '*' with specific domains like 'http://127.0.0.1:5501' for tighter security)
header('Access-Control-Allow-Methods: POST, OPTIONS');  // Allow POST and OPTIONS methods
header('Access-Control-Allow-Headers: Content-Type');  // Allow Content-Type header

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Send response and exit
    http_response_code(200); // OK
    exit();
}

// Response array
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the raw POST data
    $inputData = file_get_contents('php://input');
    file_put_contents('php://stderr', "RAW POST DATA: $inputData\n");

    // Decode the JSON data
    $inputData = json_decode($inputData, true);
    
    // Log the decoded data
    file_put_contents('php://stderr', "DECODED DATA: " . print_r($inputData, true) . "\n");

    // Check if all required fields are set
    if (
        isset($inputData['first_name'], $inputData['last_name'], 
              $inputData['full_address'], $inputData['state'], $inputData['city'], 
              $inputData['landmark'], $inputData['pincode'], $inputData['phone'])
    ) {

        // Prepare the address data to be inserted
        $addressData = [
            'first_name' => $inputData['first_name'],
            'last_name' => $inputData['last_name'],
            'full_address' => $inputData['full_address'],
            'state' => $inputData['state'],
            'city' => $inputData['city'],
            'landmark' => $inputData['landmark'],
            'pincode' => $inputData['pincode'],
            'phone' => $inputData['phone'],
            'created_at' => new UTCDateTime(),  // Store the current date-time as UTCDateTime
        ];

        // Insert data into MongoDB
        try {
            $collection = $db->addresses;  // Select the 'addresses' collection
            $insertResult = $collection->insertOne($addressData);
            
            if ($insertResult->getInsertedCount() === 1) {
                $response['success'] = true;
                $response['message'] = 'Address submitted successfully!';
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to submit address!';
            }

        } catch (Exception $e) {
            file_put_contents('php://stderr', "MongoDB Error: " . $e->getMessage() . "\n");
            $response['success'] = false;
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        // Log the input data to find out what is missing
        file_put_contents('php://stderr', "Missing required fields: " . print_r($inputData, true) . "\n");
        $response['success'] = false;
        $response['message'] = 'Missing required fields.';
    }

    echo json_encode($response); // Send JSON response back to frontend
}
?>
