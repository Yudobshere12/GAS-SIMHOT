<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "working_project_schema");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query recent ratings
$sql = "SELECT rating, comment, created_at FROM gas_simhot_ratings ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
$ratings = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }
}
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
    </main>

    <script src="script.php"></script>
</body>
</html>