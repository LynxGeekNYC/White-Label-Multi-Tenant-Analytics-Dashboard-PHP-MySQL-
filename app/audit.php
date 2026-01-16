<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function audit_log(int $agency_id, ?int $user_id, string $action, array $context = []): void {
  $pdo = db();
  $ip = $_SERVER['REMOTE_ADDR'] ?? null;
  $stmt = $pdo->prepare("INSERT INTO audit_log (agency_id, user_id, action, context, ip) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$agency_id, $user_id, $action, json_encode($context), $ip]);
}
