<?php
require_once dirname(__DIR__) . '/config/app.php';

function normalize_ke_sms_number(string $phone): string
{
    $digits = preg_replace('/\\D+/', '', $phone) ?? '';
    if (preg_match('/^0[17]\\d{8}$/', $digits)) {
        $digits = '254' . substr($digits, 1);
    } elseif (preg_match('/^[17]\\d{8}$/', $digits)) {
        $digits = '254' . $digits;
    }
    return preg_match('/^254[17]\\d{8}$/', $digits) ? '+' . $digits : '';
}

function send_sms(string $phone, string $message): bool
{
    $to = normalize_ke_sms_number($phone);
    $apiKey = env_value('AT_API_KEY');
    $username = env_value('AT_USERNAME', 'sandbox');
    $environment = env_value('AT_ENV', 'sandbox');
    if ($to === '' || $message === '' || $apiKey === '' || str_contains($apiKey, 'replace_me')) return false;
    if (!function_exists('curl_init')) {
        error_log('SMS delivery unavailable: PHP cURL extension is not enabled.');
        return false;
    }
    $endpoint = $environment === 'production'
        ? 'https://api.africastalking.com/version1/messaging'
        : 'https://api.sandbox.africastalking.com/version1/messaging';
    $fields = ['username' => $username, 'to' => $to, 'message' => $message];
    $senderId = env_value('AT_SENDER_ID');
    if ($senderId !== '') $fields['from'] = $senderId;
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded', 'apiKey: ' . $apiKey],
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($raw === false || $http < 200 || $http >= 300) {
        error_log("Africas Talking SMS request failed: " . ($error ?: 'HTTP ' . $http));
        return false;
    }
    $data = json_decode((string) $raw, true);
    foreach (($data['SMSMessageData']['Recipients'] ?? []) as $recipient) {
        $statusCode = (int) ($recipient['statusCode'] ?? 0);
        if ($statusCode >= 100 && $statusCode < 200) return true;
    }
    error_log('Africas Talking SMS rejected: ' . (string) ($data['SMSMessageData']['Message'] ?? 'Provider did not accept the SMS.'));
    return false;
}
