<?php
// public/up.php - Direct maintenance deactivator for Shared Hosting
$downFile = __DIR__ . '/../storage/framework/down';
$maintenanceFile = __DIR__ . '/../storage/framework/maintenance.php';

@unlink($downFile);
@unlink($maintenanceFile);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application is Live | WMS</title>
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
            color: #10b981;
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
            background: #3b82f6;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.2s ease;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h2>Application is LIVE!</h2>
        <p>Maintenance mode has been deactivated. All users can now access the ERP system normally.</p>
        <a href="/" class="btn">🚀 Go to ERP Dashboard / Login</a>
    </div>
</body>
</html>
