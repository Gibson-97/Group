<?php
// includes/layout_start.php

if (!isset($pageTitle))   $pageTitle   = "NCE Clinic";
if (!isset($headerColor)) $headerColor = "#1565c0";
if (!isset($sidebarFile)) $sidebarFile = "sidebar_admin.php";

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - NCE Clinic</title>
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        :root {
            --primary: <?= $headerColor ?>;
            --sidebar-width: 260px;
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: #333;
        }

        /* ========== HEADER ========== */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14px;
        }

        .header-right a {
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .header-right a:hover {
            background: rgba(255,255,255,0.15);
        }

        .header-right .logout-link {
            color: #c62828;
            background: #fff;
            border: 1px solid #ffcdd2;
        }

        .header-right .logout-link:hover {
            color: #fff;
            background: #c62828;
            border-color: #c62828;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: #ffffff;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 20px 0;
            z-index: 900;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 13px 25px;
            color: #444;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover {
            background: #f5f5f5;
            color: var(--primary);
        }

        .sidebar a.active {
            background: #f0f7ff;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .sidebar-alert-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            margin-left: 6px;
            padding: 0 5px;
            position: relative;
            top: -0.35em;
            border-radius: 999px;
            background: #c62828;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }

        /* ========== CARDS & COMPONENTS ========== */
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .card h2, .card h3 {
            margin-bottom: 15px;
            color: #222;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
            margin-right: 8px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .badge {
            display: inline-block;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-danger  { background: #ffebee; color: #c62828; }
        .badge-warning { background: #fff3e0; color: #ef6c00; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success { background: #e8f5e9; color: #2e7d32; }
        .alert-error   { background: #ffebee; color: #c62828; }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        input, select, textarea {
            width: 100%;
            max-width: 420px;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: white;
            padding: 22px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .stat-box h3 {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 5px;
        }
    </style>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <h2><?= e($pageTitle) ?></h2>
    <div class="header-right">
        <span><?= e($user['full_name']) ?> (<?= e($user['role']) ?>)</span>
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</header>

<!-- SIDEBAR -->
<aside class="sidebar">
    <?php include $sidebarFile; ?>
</aside>

<!-- MAIN CONTENT START -->
<main class="main-content">