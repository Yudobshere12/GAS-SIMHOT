<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        .status-badge { font-size: 0.9rem; padding: 5px 12px; border-radius: 20px; transition: 0.3s; }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.3; } }
        .gauge-text { font-size: 2.2rem; font-weight: bold; transition: 0.3s; }
        /* Style for the container of the map*/
        #map { height: 400px; width: 100%; border-radius: 10px; border: 1px solid #ddd; margin-top: 20px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4 shadow">
        <span class="navbar-brand fw-bold">GAS-SIMHOT | Admin Panel</span>
        <div class="d-flex align-items-center">
            <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4 text-center">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="text-muted text-uppercase small">System Status</h6>
                    <div id="live-status-text" class="status-badge bg-success text-white d-inline-block mx-auto mt-2">SYSTEM ONLINE</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="text-muted text-uppercase small">LPG Concentration</h6>
                    <div id="live-ppm" class="gauge-text text-primary mt-1">0 PPM</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="text-muted text-uppercase small">Management</h6>
                    <div class="mt-2">
                        <a href="clear_logs.php" class="btn btn-sm btn-danger w-100 mb-1" onclick="return confirm('Delete all history?')">🗑️ Clear Logs</a>
                        <button class="btn btn-sm btn-secondary w-100" onclick="location.reload()">🔄 Refresh</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 shadow border-0 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0 text-primary fw-bold">Activity History</h3>
                <input type="text" id="logSearch" class="form-control w-25" placeholder="🔍 Search logs..." onkeyup="filterLogs()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="logTable">
                    <thead class="table-dark"><tr><th>ID</th><th>User Member</th><th>Action</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php
                        $logs = $conn->query("SELECT l.id, u.full_name, l.action, l.created_at FROM user_activity_logs l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");
                        while($row = $logs->fetch_assoc()){
                            $isLeak = strpos($row['action'], 'Leak') !== false;
                            $style = $isLeak ? 'table-danger fw-bold text-danger' : '';
                            echo "<tr class='$style'><td>#{$row['id']}</td><td>{$row['full_name']}</td><td>{$row['action']}</td><td>{$row['created_at']}</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 shadow border-0 mb-5">
            <h3 class="text-primary fw-bold mb-3">📍 Gas Leaked Monitoring</h3>
            <div id="map"></div>
        </div>
    </div>

    <div id="admin-sticky-alert" class="alert alert-danger d-none shadow-lg" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 350px; border-left: 10px solid #721c24;">
        <div class="d-flex align-items-center">
            <div class="spinner-grow text-danger me-2 blink" style="width:1rem; height:1rem;"></div>
            <h5 class="mb-0 fw-bold">GAS LEAK DETECTED!</h5>
        </div>
        <p id="leak-msg" class="mt-2 mb-3 small"></p>
        <button class="btn btn-danger btn-sm w-100" onclick="acknowledgeSticky()">Acknowledge & Notify User</button>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    
    <script>
        let isAcknowledged = false;

        // 4. GIDUGANG: INITIALIZE ANG MAP
        const map = L.map('map').setView([8.3697, 124.8644], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        let currentMarker = null;

        function filterLogs() {
            let input = document.getElementById("logSearch").value.toLowerCase();
            let tr = document.getElementById("logTable").getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = tr[i].textContent.toLowerCase().includes(input) ? "" : "none";
            }
        }

        // 5. COMBINED: Dashboard Updates and Map Updates
        function updateDashboardAndMap() {
            fetch('check_alert.php').then(r => r.json()).then(data => {
                const alertBox = document.getElementById('admin-sticky-alert');
                const statusText = document.getElementById('live-status-text');
                const ppmText = document.getElementById('live-ppm');

                if (data.is_active == 1) {
                    // Update UI Text
                    statusText.innerText = "⚠️ EMERGENCY MODE";
                    statusText.className = "status-badge bg-danger text-white d-inline-block mx-auto blink";
                    ppmText.innerText = "450 PPM";
                    ppmText.className = "gauge-text text-danger";

                    if (!isAcknowledged && data.acknowledged_by_admin == 0) {
                        alertBox.classList.remove('d-none');
                        document.getElementById('leak-msg').innerText = "Critical alert triggered by " + data.triggered_by;
                        playAdminChime();
                    } else if (data.acknowledged_by_admin == 1) {
                        alertBox.classList.add('d-none');
                    }

                    // UPDATE MAP MARKER
                    if (data.lat && data.lng) {
                        const staffLat = parseFloat(data.lat);
                        const staffLng = parseFloat(data.lng);
                        if (currentMarker) map.removeLayer(currentMarker);
                        currentMarker = L.marker([staffLat, staffLng]).addTo(map)
                            .bindPopup(`<b>🚨 EMERGENCY: ${data.location}</b><br>Staff: ${data.triggered_by}`)
                            .openPopup();
                        map.flyTo([staffLat, staffLng], 17);
                    }

                } else {
                    // Reset UI
                    statusText.innerText = "SYSTEM ONLINE";
                    statusText.className = "status-badge bg-success text-white d-inline-block mx-auto";
                    ppmText.innerText = "0 PPM";
                    ppmText.className = "gauge-text text-primary";
                    alertBox.classList.add('d-none');
                    isAcknowledged = false;
                    
                    // Remove the map marker if there is no more leak
                    if (currentMarker) {
                        map.removeLayer(currentMarker);
                        currentMarker = null;
                    }
                }
            });
        }

        function acknowledgeSticky() {
            document.getElementById('admin-sticky-alert').classList.add('d-none');
            isAcknowledged = true;
            let fd = new FormData(); fd.append('action', 'admin_ack');
            fetch('log_action.php', { method: 'POST', body: fd });
        }

        function playAdminChime() {
            let ctx = new (window.AudioContext || window.webkitAudioContext)();
            let osc = ctx.createOscillator();
            let gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 587.33; 
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.6);
            osc.start(); osc.stop(ctx.currentTime + 0.6);
        }

        // Kada 2.5 Seconds to refresh all data
        setInterval(updateDashboardAndMap, 2500);
    </script>
</body>
</html>