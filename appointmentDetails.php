<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $collection = $db->appointments;

    // Exclude _id using projection
    $appointmentDetails = $collection->find([], ['projection' => ['_id' => 0]])->toArray();

    echo json_encode($appointmentDetails);
} else {
    echo json_encode(['error' => 'Invalid request method. Use GET to fetch data.']);
}
?>
