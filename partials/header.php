<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/csrf.php';
require_once __DIR__ . '/../app/helpers.php';

$u = current_user();
$brandLogo = $u && !empty($u['agency_logo_url']) ? $u['agency_logo_url'] : '';
$brandColor = $u && !empty($u['agency_primary_color']) ? $u['agency_primary_color'] : '#212529';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    .navbar-custom { background: <?= h($brandColor) ?> !important; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
      <?php if ($brandLogo): ?>
        <img src="<?= h($brandLogo) ?>" alt="Logo" style="height:26px;">
      <?php endif; ?>
      <span>Analytics</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <?php if ($u): ?>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>

        <?php if (($u['user_type'] ?? 'agency') !== 'client'): ?>
          <li class="nav-item"><a class="nav-link" href="clients.php">Clients</a></li>
          <li class="nav-item"><a class="nav-link" href="integrations.php">Integrations</a></li>
          <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
          <?php if (in_array($u['agency_role'], ['agency_owner','agency_admin'], true)): ?>
            <li class="nav-item"><a class="nav-link" href="agency_settings.php">Agency Settings</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <div class="d-flex">
        <span class="navbar-text me-3"><?= h($u['name']) ?></span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Log Out</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container-fluid py-3">
  <input type="hidden" id="csrf_token" value="<?= h(csrf_token()) ?>">
