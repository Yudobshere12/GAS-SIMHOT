<?php header("Content-type: application/javascript"); ?>
function simulateLeak() {
    let gasLevel = Math.floor(Math.random() * (1000 - 400 + 1)) + 400;
    updateDisplay(gasLevel);
}

function resetSystem() {
    updateDisplay(120);
}

function updateDisplay(value) {
    const display = document.getElementById('gas-value');
    const indicator = document.getElementById('status-indicator');
    const alertText = document.getElementById('alert-text');

    display.innerText = value;

    if (value >= 400) {
        indicator.className = 'danger';
        alertText.innerText = "WARNING: LPG LEAK DETECTED!";
        alertText.style.color = "red";
    } else {
        indicator.className = 'safe';
        alertText.innerText = "Status: System Normal";
        alertText.style.color = "black";
    }
}