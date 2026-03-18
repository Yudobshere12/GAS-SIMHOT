<?php
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Gidugangan og JOIN para makuha ang coordinates sa staff gikan sa users table
$query = "SELECT s.*, u.lat, u.lng 
          FROM system_status s 
          LEFT JOIN users u ON s.triggered_by = u.full_name 
          WHERE s.id = 1";

$res = $conn->query($query);
echo json_encode($res->fetch_assoc());
?>