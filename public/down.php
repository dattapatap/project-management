<?php
// public/down.php - Direct maintenance activator for Shared Hosting
$downFile = __DIR__ . '/../storage/framework/down';
$maintenanceFile = __DIR__ . '/../storage/framework/maintenance.php';

$data = [
    'time' => time(),
    'message' => 'Under Scheduled Maintenance',
    'retry' => null,
    'allowed' => [],
    'secret' => null,
    'status' => 503,
    'template' => null,
];

file_put_contents($downFile, json_encode($data, JSON_PRETTY_PRINT));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode Activated | WMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 1.5rem;
            color: #f59e0b;
            margin-bottom: 12px;
        }
        p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            background: #10b981;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.2s ease;
        }
        .btn:hover {
            background: #059669;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h2>Maintenance Mode Activated</h2>
        <p>The application is now <strong>DOWN</strong> for regular visitors. They will see the maintenance screen until you bring it back online.</p>
        <a href="up.php" class="btn">🟢 Bring Site Back LIVE (up.php)</a>
    </div>
</body>
</html>
