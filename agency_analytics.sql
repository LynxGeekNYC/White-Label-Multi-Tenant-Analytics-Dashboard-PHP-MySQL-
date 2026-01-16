CREATE DATABASE IF NOT EXISTS agency_analytics
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE agency_analytics;

SET sql_mode = 'STRICT_ALL_TABLES';

-- Agencies (branding included)
CREATE TABLE IF NOT EXISTS agencies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  logo_url VARCHAR(255) NULL,
  primary_color VARCHAR(20) NULL,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Multiple domains per agency
CREATE TABLE IF NOT EXISTS agency_domains (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  domain VARCHAR(190) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_domain (domain),
  KEY idx_agency (agency_id),
  CONSTRAINT fk_domains_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clients (workspaces)
CREATE TABLE IF NOT EXISTS clients (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_clients_agency (agency_id),
  CONSTRAINT fk_clients_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users: agency users and optional client-only logins
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL,
  name VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('agency','client') NOT NULL DEFAULT 'agency',
  client_id BIGINT UNSIGNED NULL,
  agency_role ENUM('agency_owner','agency_admin','agency_manager','agency_viewer') NOT NULL DEFAULT 'agency_viewer',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_agency_email (agency_id, email),
  KEY idx_users_client_id (client_id),
  CONSTRAINT fk_users_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
  CONSTRAINT fk_users_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subuser access per client (agency users only)
CREATE TABLE IF NOT EXISTS client_user_access (
  client_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  client_role ENUM('client_admin','client_viewer') NOT NULL DEFAULT 'client_viewer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (client_id, user_id),
  CONSTRAINT fk_cua_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
  CONSTRAINT fk_cua_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Integrations
CREATE TABLE IF NOT EXISTS integrations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  provider ENUM('stripe','ga4') NOT NULL,
  status ENUM('connected','disconnected','error') NOT NULL DEFAULT 'disconnected',
  display_name VARCHAR(150) NULL,
  last_sync_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_client_provider (client_id, provider),
  KEY idx_integrations_agency (agency_id),
  CONSTRAINT fk_integrations_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
  CONSTRAINT fk_integrations_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Encrypted credentials (AES-256-GCM)
CREATE TABLE IF NOT EXISTS integration_credentials (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  integration_id BIGINT UNSIGNED NOT NULL,
  credential_type ENUM('oauth','api_key','json') NOT NULL,
  access_token_enc MEDIUMTEXT NULL,
  refresh_token_enc MEDIUMTEXT NULL,
  api_key_enc MEDIUMTEXT NULL,
  json_blob_enc MEDIUMTEXT NULL,
  expires_at DATETIME NULL,
  scopes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  CONSTRAINT fk_creds_integration FOREIGN KEY (integration_id) REFERENCES integrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Metrics
CREATE TABLE IF NOT EXISTS metric_points (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  provider VARCHAR(32) NOT NULL,
  metric_key VARCHAR(64) NOT NULL,
  granularity ENUM('day','hour') NOT NULL DEFAULT 'day',
  ts DATETIME NOT NULL,
  value DECIMAL(18,4) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_point (client_id, provider, metric_key, granularity, ts),
  KEY idx_points_client_ts (client_id, ts),
  CONSTRAINT fk_points_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
  CONSTRAINT fk_points_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Share links (token stored as hash only)
CREATE TABLE IF NOT EXISTS dashboard_share_links (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  created_by_user_id BIGINT UNSIGNED NULL,
  token_hash CHAR(64) NOT NULL,
  label VARCHAR(120) NULL,
  expires_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_token_hash (token_hash),
  KEY idx_client (client_id),
  KEY idx_agency (agency_id),
  CONSTRAINT fk_share_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE,
  CONSTRAINT fk_share_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit
CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  agency_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  context JSON NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_agency (agency_id),
  CONSTRAINT fk_audit_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed demo agency
INSERT INTO agencies (name, slug, logo_url, primary_color)
VALUES ('Demo Agency', 'demo-agency', 'https://via.placeholder.com/160x40?text=Demo+Agency', '#0d6efd');

SET @agency_id = LAST_INSERT_ID();

-- Seed a demo domain (optional)
-- This must be a domain you control. Otherwise remove this insert.
-- INSERT INTO agency_domains (agency_id, domain, is_primary) VALUES (@agency_id, 'demo.local', 1);

-- Seed owner user
-- password: Admin123!
INSERT INTO users (agency_id, email, name, password_hash, user_type, agency_role)
VALUES (
  @agency_id,
  'owner@demo.com',
  'Agency Owner',
  '$2y$10$Z0zXx2o2p5X2Kx9yGqfL8e8F3U0Jq5wqf/9u6cT1yN7h5r5m5kViy',
  'agency',
  'agency_owner'
);

-- Seed one client
INSERT INTO clients (agency_id, name) VALUES (@agency_id, 'Client A');
SET @client_id = LAST_INSERT_ID();

-- Seed integrations for the client
INSERT INTO integrations (agency_id, client_id, provider, status, display_name)
VALUES
(@agency_id, @client_id, 'stripe', 'disconnected', 'Stripe'),
(@agency_id, @client_id, 'ga4', 'disconnected', 'Google Analytics 4');
