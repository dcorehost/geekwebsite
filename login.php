<?php
require 'db.php'; // Include the MongoDB connection
session_start();

// Add the necessary namespace for MongoDB BSON
use MongoDB\BSON\UTCDateTime;

// CORS headers to allow requests from different origins
header('Access-Control-Allow-Origin: *');  // Allow all origins
header('Access-Control-Allow-Methods: POST');  // Allow only POST requests
header('Access-Control-Allow-Headers: Content-Type');  // Allow only Content-Type header

// Response array
$response = [];

// Handle preflight request (CORS check before actual POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data from the body (JSON)
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['email'], $inputData['password'])) {
        $email = $inputData['email'];
        $password = $inputData['password'];

        // Check if the user already exists
        $usersCollection = $db->users;
        $existingUser = $usersCollection->findOne(['email' => $email]);

        if ($existingUser) {
            $response = ['error' => 'User with this email already exists.'];
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Store the user in the database
            $newUser = [
                'email' => $email,
                'password' => $hashedPassword,
                'createdAt' => new UTCDateTime(),
                'updatedAt' => new UTCDateTime(),
            ];

            $usersCollection->insertOne($newUser);

            // Send a successful response (no redirect, just a success message)
            $response = ['success' => 'User registered successfully'];
        }
    } else {
        $response = ['error' => 'Email and password are required.'];
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
