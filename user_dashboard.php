<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'staff') { 
    header("Location: index.php"); 
    exit(); 
}
$uid = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .circle { width: 140px; height: 140px; border-radius: 50%; background: green; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 20px auto; transition: 0.4s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.2; } }
        .progress { height: 20px; border-radius: 10px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow d-flex justify-content-between">
        <span class="navbar-brand">Staff Terminal</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm px-3">Logout</a>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-7 text-center">
                <div id="leak-alert" class="alert alert-danger d-none blink shadow"><h4>⚠️ LEAK DETECTED!</h4></div>
                <div id="admin-feedback" class="alert alert-info d-none shadow-sm"><strong>✅ Admin Notified</strong> <span id="ack-time" class="badge bg-info ms-2"></span></div>
                
                <div class="card p-4 shadow border-0 mb-4">
                    <h6 class="text-muted">Gas Concentration</h6>
                    <div class="progress mb-3"><div id="ppm-bar" class="progress-bar bg-success" style="width: 0%;"></div></div>
                    <div id="ppm" class="circle">0 PPM</div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-uppercase">Your Current Location / Room:</label>
                        <input type="text" id="manual-location" class="form-control form-control-lg border-primary" placeholder="e.g. Kitchen, Lab A, Room 302" value="Main Floor">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-lg shadow" onclick="triggerLeak()">Simulate Leak</button>
                        <button class="btn btn-secondary" onclick="res()">System Reset</button>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card shadow border-0 p-3 h-100">
                    <h6 class="fw-bold mb-3">Your Recent Actions</h6>
                    <table class="table table-sm small">
                        <tbody>
                            <?php
                            $logs = $conn->query("SELECT action, created_at FROM user_activity_logs WHERE user_id = '$uid' ORDER BY created_at DESC LIMIT 10");
                            if ($logs) {
                                while($l = $logs->fetch_assoc()) {
                                    echo "<tr><td>{$l['action']}</td><td class='text-muted small'>{$l['created_at']}</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let audioCtx = null, pulse = null;

        function updateUI(p) {
            document.getElementById('ppm').innerText = p + " PPM";
            document.getElementById('ppm-bar').style.width = (p/1000*100) + "%";
            if (p >= 400) {
                document.getElementById('ppm').style.background = "red";
                document.getElementById('leak-alert').classList.remove('d-none');
                if(!pulse) startBuzzer();
            } else {
                document.getElementById('ppm').style.background = "green";
                document.getElementById('leak-alert').classList.add('d-none');
                stopBuzzer();
            }
        }

        function triggerLeak() {
            updateUI(450);
            logAction("Leak Detected");
        }

        function res() { 
            updateUI(0); 
            document.getElementById('admin-feedback').classList.add('d-none'); 
            logAction("System Reset"); 
        }

        function logAction(act) { 
            let locValue = document.getElementById('manual-location').value;
            let fd = new FormData(); 
            fd.append('action', act); 
            fd.append('location', locValue); // Send the text box value instead of GPS

            fetch('log_action.php', { method: 'POST', body: fd })
            .then(() => setTimeout(() => location.reload(), 1000)); 
        }

        function startBuzzer() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            pulse = setInterval(() => {
                let o = audioCtx.createOscillator(); let g = audioCtx.createGain();
                o.type = 'square'; o.frequency.value = 880; 
                g.gain.setValueAtTime(0.1, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.5);
                o.connect(g); g.connect(audioCtx.destination); 
                o.start(); o.stop(audioCtx.currentTime + 0.5);
            }, 600);
        }
        function stopBuzzer() { clearInterval(pulse); pulse = null; }
    </script>
</body>
</html>