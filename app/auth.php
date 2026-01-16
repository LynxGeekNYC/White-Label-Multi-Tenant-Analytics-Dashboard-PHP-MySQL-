<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_session(): void {
  $cfg = require __DIR__ . '/config.php';
  session_name($cfg['session_name']);
  session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
  ]);
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function require_login(): void {
  if (empty($_SESSION['user_id']) || empty($_SESSION['agency_id'])) {
    header('Location: login.php');
    exit;
  }
}

function current_user(): ?array {
  if (empty($_SESSION['user_id'])) return null;
  return [
    'user_id' => (int)$_SESSION['user_id'],
    'agency_id' => (int)$_SESSION['agency_id'],
    'agency_role' => (string)$_SESSION['agency_role'],
    'name' => (string)($_SESSION['name'] ?? ''),
    'email' => (string)($_SESSION['email'] ?? ''),
    'user_type' => (string)($_SESSION['user_type'] ?? 'agency'),
    'client_id' => $_SESSION['client_id'] ?? null,
    'agency_slug' => (string)($_SESSION['agency_slug'] ?? ''),
    'agency_logo_url' => (string)($_SESSION['agency_logo_url'] ?? ''),
    'agency_primary_color' => (string)($_SESSION['agency_primary_color'] ?? '#0d6efd'),
  ];
}

function login_attempt(string $email, string $password, string $agency_slug): array {
  $pdo = db();

  $stmt = $pdo->prepare("SELECT * FROM agencies WHERE slug = ? AND status = 'active' LIMIT 1");
  $stmt->execute([$agency_slug]);
  $agency = $stmt->fetch();
  if (!$agency) return ['ok' => false, 'error' => 'Invalid agency.'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE agency_id = ? AND email = ? AND is_active = 1 LIMIT 1");
  $stmt->execute([(int)$agency['id'], $email]);
  $user = $stmt->fetch();
  if (!$user) return ['ok' => false, 'error' => 'Invalid login.'];

  if (!password_verify($password, $user['password_hash'])) {
    return ['ok' => false, 'error' => 'Invalid login.'];
  }

  if ($user['user_type'] === 'client') {
    if (empty($user['client_id'])) return ['ok' => false, 'error' => 'Client account misconfigured.'];
    $stmt = $pdo->prepare("SELECT 1 FROM clients WHERE id=? AND agency_id=? AND status='active' LIMIT 1");
    $stmt->execute([(int)$user['client_id'], (int)$agency['id']]);
    if (!$stmt->fetchColumn()) return ['ok' => false, 'error' => 'Client is inactive or invalid.'];
  }

  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['agency_id'] = (int)$user['agency_id'];
  $_SESSION['agency_role'] = (string)$user['agency_role'];
  $_SESSION['name'] = (string)$user['name'];
  $_SESSION['email'] = (string)$user['email'];
  $_SESSION['user_type'] = (string)$user['user_type'];
  $_SESSION['client_id'] = $user['client_id'] ? (int)$user['client_id'] : null;

  $_SESSION['agency_slug'] = (string)$agency['slug'];
  $_SESSION['agency_logo_url'] = (string)($agency['logo_url'] ?? '');
  $_SESSION['agency_primary_color'] = (string)($agency['primary_color'] ?? '#0d6efd');

  $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([(int)$user['id']]);

  return ['ok' => true];
}

function logout(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
}
