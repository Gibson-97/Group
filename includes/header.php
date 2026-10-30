<?php
// includes/header.php
// Usage: 
// $pageTitle = "Dashboard";
// $headerColor = "#1565c0";   // optional
// include '../includes/header.php';

if (!isset($pageTitle)) $pageTitle = "NCE Clinic";
if (!isset($headerColor)) $headerColor = "#1565c0";
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - NCE Clinic</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; }
        .header {
            background: <?= $headerColor ?>;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h2 { font-size: 22px; }
        .header a { color: white; text-decoration: none; margin-left: 15px; }
        .header a:hover { text-decoration: underline; }
        .header a.logout-link {
            display: inline-block;
            padding: 8px 12px;
            color: #c62828;
            background: #fff;
            border: 1px solid #ffcdd2;
            border-radius: 6px;
        }
        .header a.logout-link:hover {
            color: #fff;
            background: #c62828;
            border-color: #c62828;
            text-decoration: none;
        }
        .container { display: flex; min-height: calc(100vh - 60px); }
        .content { flex: 1; padding: 30px; }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: <?= $headerColor ?>;
            color: #c62828;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        .btn:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: left; }
        th { background: <?= $headerColor ?>; color: white; }
        .alert { padding: 14px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-danger { background: #ffebee; color: #c62828; }
        .badge-warning { background: #fff3e0; color: #ef6c00; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, select, textarea {
            width: 100%;
            max-width: 400px;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }
        @media (max-width: 700px) {
            .header { padding: 12px 16px; gap: 10px; }
            .container { flex-direction: column; }
            .content { width: 100%; min-width: 0; padding: 20px 16px; }
            input, select, textarea { max-width: 100%; }
            table { display: block; max-width: 100%; overflow-x: auto; white-space: nowrap; }
        }
        @media (max-width: 480px) {
            .header { align-items: flex-start; flex-direction: column; }
            .content { padding: 16px 12px; }
            .card { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?= e($pageTitle) ?></h2>
        <div>
            <?= e($user['full_name']) ?> (<?= e($user['role']) ?>)
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">