<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

if (isset($_SESSION['user_id']) && isset($_POST['action'])) {
    $uid = $_SESSION['user_id'];
    $name = $_SESSION['full_name'];
    $act = $conn->real_escape_string($_POST['action']);
    $loc = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : 'Unknown';

    if ($act !== "admin_ack") {
        $conn->query("INSERT INTO user_activity_logs (user_id, action) VALUES ('$uid', '$act')");
    }

    if ($act == "Leak Detected") {
        $conn->query("UPDATE system_status SET is_active = 1, triggered_by = '$name', location = '$loc', acknowledged_by_admin = 0, ack_time = NULL WHERE id = 1");
    } 
    elseif ($act == "System Reset") {
        $conn->query("UPDATE system_status SET is_active = 0, acknowledged_by_admin = 0, ack_time = NULL WHERE id = 1");
    }
}
?>