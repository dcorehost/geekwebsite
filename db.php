<?php
require 'vendor/autoload.php'; // Load Composer's autoloader for MongoDB

// MongoDB connection URI
$uri = "mongodb+srv://dcorehost2:RgYqIq2DgboYeXfv@cluster0.szg5u.mongodb.net/geek_website?retryWrites=true&w=majority&appName=Cluster0";

try {
    // Create a new MongoDB client instance
    $client = new MongoDB\Client($uri);

    // Select the database
    $db = $client->geek_website;

    // Ensure no output other than response for your API
} catch (Exception $e) {
    // Handle connection error (output will be handled in the PHP script that uses this connection)
    exit('MongoDB connection failed: ' . $e->getMessage());
}
?>
