<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'staff') { header("Location: index.php"); exit(); }
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
    <nav class="navbar navbar-dark bg-primary px-4 shadow"><span class="navbar-brand">Staff Terminal</span></nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-7 text-center">
                <div id="leak-alert" class="alert alert-danger d-none blink shadow"><h4>⚠️ LEAK DETECTED!</h4></div>
                <div id="admin-feedback" class="alert alert-info d-none shadow-sm"><strong>✅ Admin Notified</strong> <span id="ack-time" class="badge bg-info ms-2"></span></div>
                
                <div class="card p-4 shadow border-0 mb-4">
                    <h6 class="text-muted">Gas Concentration</h6>
                    <div class="progress mb-3"><div id="ppm-bar" class="progress-bar bg-success" style="width: 0%;"></div></div>
                    <div id="ppm" class="circle">0 PPM</div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-lg shadow" onclick="sim()">Simulate Leak</button>
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
                            while($l = $logs->fetch_assoc()) echo "<tr><td>{$l['action']}</td><td class='text-muted'>{$l['created_at']}</td></tr>";
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
        function startBuzzer() {
            if (!audioCtx) audioCtx = new AudioContext();
            pulse = setInterval(() => {
                let o = audioCtx.createOscillator(); let g = audioCtx.createGain();
                o.type = 'square'; o.frequency.value = 880; g.gain.setValueAtTime(0.1, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.5);
                o.connect(g); g.connect(audioCtx.destination); o.start(); o.stop(audioCtx.currentTime + 0.5);
            }, 600);
        }
        function stopBuzzer() { clearInterval(pulse); pulse = null; }
        function sim() { updateUI(450); log("Leak Detected"); }
        function res() { updateUI(0); document.getElementById('admin-feedback').classList.add('d-none'); log("System Reset"); }
        function log(a) { 
            let fd = new FormData(); fd.append('action', a); 
            fetch('log_action.php', { method: 'POST', body: fd }).then(()=>setTimeout(()=>location.reload(), 1000)); 
        }
        
        setInterval(() => {
            fetch('check_alert.php').then(r => r.json()).then(data => {
                let box = document.getElementById('admin-feedback');
                if (data.is_active == 1 && data.acknowledged_by_admin == 1) {
                    box.classList.remove('d-none');
                    document.getElementById('ack-time').innerText = data.ack_time;
                } else box.classList.add('d-none');
            });
        }, 3000);
    </script>
</body>
</html>