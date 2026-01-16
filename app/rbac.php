<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function can_manage_users(string $role): bool {
  return in_array($role, ['agency_owner','agency_admin'], true);
}

function can_manage_integrations(string $role): bool {
  return in_array($role, ['agency_owner','agency_admin'], true);
}

function can_view_all_clients(string $role): bool {
  return in_array($role, ['agency_owner','agency_admin','agency_manager'], true);
}

function can_manage_share_links(string $role): bool {
  return in_array($role, ['agency_owner','agency_admin','agency_manager'], true);
}

function deny_client_users(): void {
  if (!empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'client') {
    header('Location: index.php');
    exit;
  }
}

function can_access_client(int $agency_id, int $user_id, string $agency_role, int $client_id): bool {
  $pdo = db();

  $stmt = $pdo->prepare("SELECT user_type, client_id FROM users WHERE id=? AND agency_id=? AND is_active=1 LIMIT 1");
  $stmt->execute([$user_id, $agency_id]);
  $urow = $stmt->fetch();
  if (!$urow) return false;

  if ($urow['user_type'] === 'client') {
    return ((int)$urow['client_id'] === (int)$client_id);
  }

  if (can_view_all_clients($agency_role)) return true;

  $stmt = $pdo->prepare("
    SELECT 1
    FROM client_user_access cua
    INNER JOIN clients c ON c.id = cua.client_id
    WHERE cua.user_id = ? AND cua.client_id = ? AND c.agency_id = ? AND c.status = 'active'
    LIMIT 1
  ");
  $stmt->execute([$user_id, $client_id, $agency_id]);
  return (bool)$stmt->fetchColumn();
}
