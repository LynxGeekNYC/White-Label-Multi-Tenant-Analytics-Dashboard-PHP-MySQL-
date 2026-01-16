<?php
declare(strict_types=1);

return [
  'db' => [
    'host' => '127.0.0.1',
    'name' => 'agency_analytics',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],

  // 64+ hex chars recommended (32 bytes). Generate: openssl rand -hex 32
  'app_enc_key' => 'REPLACE_WITH_A_LONG_RANDOM_HEX_STRING_64PLUS',

  'session_name' => 'agency_dash_sess',

  // Cron protection
  'cron_key' => 'REPLACE_WITH_CRON_KEY',
];
