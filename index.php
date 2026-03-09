<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "working_project_schema");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query recent ratings
$sql_ratings = "SELECT rating, comment, created_at FROM gas_simhot_ratings ORDER BY created_at DESC LIMIT 5";
$result_ratings = $conn->query($sql_ratings);
$ratings = $result_ratings ? $result_ratings->fetch_all(MYSQLI_ASSOC) : [];

// Query users
$sql_users = "SELECT id, username, updated_at FROM users ORDER BY updated_at DESC LIMIT 10";
$result_users = $conn->query($sql_users);
$users = $result_users ? $result_users->fetch_all(MYSQLI_ASSOC) : [];

// Query user activity logs
$sql_logs = "SELECT id, user_id, action, created_at FROM user_activity_logs ORDER BY created_at DESC LIMIT 10";
$result_logs = $conn->query($sql_logs);
$logs = $result_logs ? $result_logs->fetch_all(MYSQLI_ASSOC) : [];

// Query admins
$sql_admins = "SELECT id, username, updated_at FROM admin ORDER BY updated_at DESC LIMIT 10";
$result_admins = $conn->query($sql_admins);
$admins = $result_admins ? $result_admins->fetch_all(MYSQLI_ASSOC) : [];

// Query sessions
$sql_sessions = "SELECT id, user_id, created_at, expires_at FROM sessions ORDER BY created_at DESC LIMIT 10";
$result_sessions = $conn->query($sql_sessions);
$sessions = $result_sessions ? $result_sessions->fetch_all(MYSQLI_ASSOC) : [];

// Query user profiles
$sql_profiles = "SELECT id, user_id, email, first_name, last_name, created_at FROM user_profiles ORDER BY created_at DESC LIMIT 10";
$result_profiles = $conn->query($sql_profiles);
$profiles = $result_profiles ? $result_profiles->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gas-Simhot | LPG Leak Detector</title>
    <link rel="stylesheet" href="style.php">
</head>
<body>
    <nav>
        <h1>Gas-Simhot System</h1>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">About</a></li>
        </ul>
    </nav>

    <main class="container">
        <section class="monitor-card">
            <h2>LPG Concentration Level</h2>
            <div id="status-indicator" class="safe">
                <span id="gas-value">0</span> PPM
            </div>
            <p id="alert-text">Status: System Normal</p>
            <button onclick="simulateLeak()">Simulate Gas Leak</button>
            <button onclick="resetSystem()">Reset System</button>
        </section>

        <section class="ratings-card">
            <h2>Recent Ratings</h2>
            <?php if (empty($ratings)): ?>
                <p>No ratings yet.</p>
            <?php else: ?>
                <?php foreach($ratings as $rating): ?>
                    <div class="rating">
                        <strong>Rating: <?php echo htmlspecialchars($rating['rating']); ?>/5</strong><br>
                        <?php echo htmlspecialchars($rating['comment']); ?><br>
                        <small><?php echo htmlspecialchars($rating['created_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="table-section">
            <h2>Users</h2>
            <table>
                <tr><th>ID</th><th>Username</th><th>Updated At</th></tr>
                <?php foreach($users as $user): ?>
                <tr><td><?php echo htmlspecialchars($user['id']); ?></td><td><?php echo htmlspecialchars($user['username']); ?></td><td><?php echo htmlspecialchars($user['updated_at']); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </section>

        <section class="table-section">
            <h2>User Activity Logs</h2>
            <table>
                <tr><th>ID</th><th>User ID</th><th>Action</th><th>Created At</th></tr>
                <?php foreach($logs as $log): ?>
                <tr><td><?php echo htmlspecialchars($log['id']); ?></td><td><?php echo htmlspecialchars($log['user_id']); ?></td><td><?php echo htmlspecialchars($log['action']); ?></td><td><?php echo htmlspecialchars($log['created_at']); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </section>

        <section class="table-section">
            <h2>Admins</h2>
            <table>
                <tr><th>ID</th><th>Username</th><th>Updated At</th></tr>
                <?php foreach($admins as $admin): ?>
                <tr><td><?php echo htmlspecialchars($admin['id']); ?></td><td><?php echo htmlspecialchars($admin['username']); ?></td><td><?php echo htmlspecialchars($admin['updated_at']); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </section>

        <section class="table-section">
            <h2>Sessions</h2>
            <table>
                <tr><th>ID</th><th>User ID</th><th>Created At</th><th>Expires At</th></tr>
                <?php foreach($sessions as $session): ?>
                <tr><td><?php echo htmlspecialchars($session['id']); ?></td><td><?php echo htmlspecialchars($session['user_id']); ?></td><td><?php echo htmlspecialchars($session['created_at']); ?></td><td><?php echo htmlspecialchars($session['expires_at'] ?? 'N/A'); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </section>

        <section class="table-section">
            <h2>User Profiles</h2>
            <table>
                <tr><th>ID</th><th>User ID</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Created At</th></tr>
                <?php foreach($profiles as $profile): ?>
                <tr><td><?php echo htmlspecialchars($profile['id']); ?></td><td><?php echo htmlspecialchars($profile['user_id']); ?></td><td><?php echo htmlspecialchars($profile['email']); ?></td><td><?php echo htmlspecialchars($profile['first_name'] ?? ''); ?></td><td><?php echo htmlspecialchars($profile['last_name'] ?? ''); ?></td><td><?php echo htmlspecialchars($profile['created_at']); ?></td></tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <script src="script.php"></script>
</body>
</html>