<?php
require 'vendor/autoload.php'; // Include Composer autoload to load MongoDB classes

try {
    // Create a new MongoDB client instance
    $client = new MongoDB\Client("mongodb://localhost:27017");

    // Try to access a database (for example: 'test')
    $database = $client->selectDatabase('test');
    
    echo "Connected to MongoDB successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
