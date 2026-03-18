<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Ensure that only the manager can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') { 
    header("Location: index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand fw-bold">🛡️ MANAGER COMMAND CENTER</span>
            <div class="d-flex align-items-center">
                <span class="me-3">Manager: <?php echo $_SESSION['full_name']; ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card bg-secondary text-white shadow">
                    <div class="card-header fw-bold bg-dark">System-Wide Activity Audit</div>
                    <div class="card-body">
                        <table class="table table-dark table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>User Member</th>
                                    <th>Role</th>
                                    <th>Action Performed</th>
                                    <th>Location</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_logs = $conn->query("SELECT u.full_name, u.role, l.action, l.created_at, u.location 
                                    FROM user_activity_logs l 
                                    JOIN users u ON l.user_id = u.id 
                                    ORDER BY l.created_at DESC LIMIT 15");
                                while($row = $all_logs->fetch_assoc()) {
                                    $role_badge = ($row['role'] == 'admin') ? 'bg-warning text-dark' : 'bg-info text-dark';
                                    echo "<tr>
                                        <td>{$row['full_name']}</td>
                                        <td><span class='badge {$role_badge}'>".strtoupper($row['role'])."</span></td>
                                        <td>{$row['action']}</td>
                                        <td>{$row['location']}</td>
                                        <td>{$row['created_at']}</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-5">
                <div class="card bg-secondary text-white shadow">
                    <div class="card-header fw-bold bg-dark">User Overview & Management</div>
                    <div class="card-body">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Current Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = $conn->query("SELECT full_name, role, location FROM users");
                                while($u = $users->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$u['full_name']}</td>
                                        <td>".strtoupper($u['role'])."</td>
                                        <td>{$u['location']}</td>
                                        <td><span class='badge bg-success text-white'>Active</span></td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>