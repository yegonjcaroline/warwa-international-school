<?php
declare(strict_types=1);
function load_env(string $file): void
{
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key); $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) putenv($key . '=' . $value);
    }
}
load_env(dirname(__DIR__) . '/.env');
function env_value(string $key, string $default = ''): string { $value = getenv($key); return $value === false ? $default : $value; }
function app_url(string $path = ''): string { return rtrim(env_value('APP_URL', 'http://localhost/Warwa-International-School'), '/') . '/' . ltrim($path, '/'); }
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
    session_start();
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">'; }
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) { http_response_code(419); exit('Your session expired. Go back and try again.'); }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(?string $role = null): void {
    $user = current_user();
    if (!$user) { header('Location: ' . app_url('login.php')); exit; }
    if ($role !== null && $user['role'] !== $role) { http_response_code(403); exit('You do not have permission to view this page.'); }
    if (isset($GLOBALS['pdo'])) { $check=$GLOBALS['pdo']->prepare('SELECT is_active FROM users WHERE id=?'); $check->execute([(int)$user['id']]); if (!(int)$check->fetchColumn()) { $_SESSION=[]; session_destroy(); header('Location: ' . app_url('login.php')); exit; } }
}
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function redirect(string $path): never { header('Location: ' . app_url($path)); exit; }
