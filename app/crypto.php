<?php
declare(strict_types=1);

function crypto_key_bin(): string {
  $cfg = require __DIR__ . '/config.php';
  $hex = $cfg['app_enc_key'];
  $bin = @hex2bin($hex);
  if ($bin === false || strlen($bin) < 32) {
    throw new RuntimeException('APP_ENC_KEY must be hex and at least 32 bytes (64 hex chars).');
  }
  return $bin;
}

function enc(string $plaintext): string {
  $key = crypto_key_bin();
  $iv = random_bytes(12);
  $tag = '';
  $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
  if ($cipher === false) throw new RuntimeException('Encryption failed.');
  return base64_encode($iv . $tag . $cipher);
}

function dec(string $b64): string {
  $raw = base64_decode($b64, true);
  if ($raw === false || strlen($raw) < 28) throw new RuntimeException('Invalid ciphertext.');
  $iv = substr($raw, 0, 12);
  $tag = substr($raw, 12, 16);
  $cipher = substr($raw, 28);
  $plain = openssl_decrypt($cipher, 'aes-256-gcm', crypto_key_bin(), OPENSSL_RAW_DATA, $iv, $tag);
  if ($plain === false) throw new RuntimeException('Decryption failed.');
  return $plain;
}
