<?php
require 'db.php'; // Include the MongoDB connection
session_start();

// Add the necessary namespace for MongoDB BSON
use MongoDB\BSON\UTCDateTime;

// CORS headers to allow requests from different origins
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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
        isset($inputData['name'], $inputData['phone'], $inputData['email'], 
              $inputData['device'], $inputData['issue'])
    ) {

        // Prepare the appointment data to be inserted
        $appointmentData = [
            'name' => $inputData['name'],
            'phone' => $inputData['phone'],
            'email' => $inputData['email'],
            'device' => $inputData['device'],
            'issue' => $inputData['issue'],
            'created_at' => new UTCDateTime(),  // Store the current date-time as UTCDateTime
        ];

        // Insert data into MongoDB
        try {
            $collection = $db->appointments;  // Select the 'appointments' collection
            $insertResult = $collection->insertOne($appointmentData);

            if ($insertResult->getInsertedCount() === 1) {
                $response['success'] = true;
                $response['message'] = 'Appointment booked successfully!';
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to book appointment!';
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
