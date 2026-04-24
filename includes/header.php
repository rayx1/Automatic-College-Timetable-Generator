<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
$currentPath = $_SERVER['PHP_SELF'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automatic College Timetable Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/automatic-college-timetable-generator/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/automatic-college-timetable-generator/index.php">Timetable Generator</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="/automatic-college-timetable-generator/<?= e($user['role']); ?>/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($user): ?>
                    <li class="nav-item"><span class="nav-link"><?= e($user['name']); ?> (<?= strtoupper(e($user['role'])); ?>)</span></li>
                    <li class="nav-item"><a class="nav-link" href="/automatic-college-timetable-generator/auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/automatic-college-timetable-generator/auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success"><?= e($message); ?></div>
    <?php endif; ?>
    <?php if ($message = flash('danger')): ?>
        <div class="alert alert-danger"><?= e($message); ?></div>
    <?php endif; ?>
    <?php if ($message = flash('warning')): ?>
        <div class="alert alert-warning"><?= e($message); ?></div>
    <?php endif; ?>

