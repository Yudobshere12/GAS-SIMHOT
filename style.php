<?php header("Content-type: text/css"); ?>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #f4f4f4;
    text-align: center;
}

nav {
    background: #2c3e50;
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

ul { list-style: none; display: flex; gap: 20px; }
a { color: white; text-decoration: none; }

.container { padding: 50px; }

.monitor-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    display: inline-block;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#status-indicator {
    font-size: 3rem;
    padding: 20px;
    border-radius: 50%;
    width: 150px;
    height: 150px;
    line-height: 150px;
    margin: 20px auto;
    color: white;
    transition: 0.3s;
}

.safe { background-color: #27ae60; }
.danger { background-color: #e74c3c; animation: blink 0.5s infinite; }

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

button {
    padding: 10px 20px;
    margin: 5px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
}

.ratings-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    margin-top: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.rating {
    margin-bottom: 10px;
    padding: 10px;
    border-bottom: 1px solid #eee;
}