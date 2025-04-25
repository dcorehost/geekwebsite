<?php
header(header: "Access-Control-Allow-Origin: *");
header(header: "Access-Control-Allow-Methods: POST, OPTIONS"); // ✅ Added OPTIONS to support preflight
header(header: "Access-Control-Allow-Headers: Content-Type");
header(header: 'Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // ✅ No Content
    exit();
}
require 'db.php'; // Assuming your database connection file is named 'db.php'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    if ($data === null) {
        echo json_encode(['error' => 'Invalid JSON data received.']);
        http_response_code(400); // Bad Request
        exit();
    }

    // Validate the received data (add more validation as needed)
    if (empty($data['issueType']) || empty($data['subject']) || empty($data['description'])) {
        echo json_encode(['error' => 'Please fill in all the required fields.']);
        http_response_code(400); // Bad Request
        exit();
    }

    $collection = $db->issues; // Assuming you have a MongoDB collection named 'issues'

    $issueData = [
        'issueType' => $data['issueType'],
        'subject' => $data['subject'],
        'description' => $data['description'],
        'submittedAt' => new MongoDB\BSON\UTCDateTime(), // Add a timestamp
        // You might want to add user information here if you have it in your session or local storage
        // 'userId' => 'user123', // Example user ID
    ];

    try {
        $insertResult = $collection->insertOne($issueData);

        if ($insertResult->getInsertedCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Issue reported successfully!']);
            http_response_code(201); // Created
            exit();
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save the issue. Please try again.']);
            http_response_code(500); // Internal Server Error
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
} else {
    echo json_encode(['error' => 'Invalid request method. Use POST to submit issues.']);
    http_response_code(405); // Method Not Allowed
}
?>