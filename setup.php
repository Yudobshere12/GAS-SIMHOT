<?php
// Database setup script for working-project-schema
// Run this with PHP to create the database and import schema

$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Set your MySQL root password
$dbname = "working_project_schema";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Read and execute SQL file
$sql_file = 'uses-cases/working_schema.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    if ($conn->multi_query($sql)) {
        echo "Schema from $sql_file imported successfully<br>";
        // Consume all results to avoid "Commands out of sync" error
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    } else {
        echo "Error importing schema: " . $conn->error . "<br>";
    }
} else {
    echo "SQL file $sql_file not found<br>";
}

$conn->close();
echo "Setup complete. You can now use the database in your PHP application.";
?>