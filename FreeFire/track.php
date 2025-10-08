<?php
$BOT_TOKEN = getenv('BOT_TOKEN') ?: '8116300105:AAGq5KV6gXF7A8B3EFYPI1qFS1ORhQxOgIY';
$CHAT_ID   = getenv('CHAT_ID')   ?: '7001397797';

function get_client_ip(){
    $headers = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ips = explode(',', $_SERVER[$h]);
            return trim($ips[0]);
        }
    }
    return 'unknown';
}

$ip = get_client_ip();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$path = $_GET['p'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
$time = gmdate('Y-m-d H:i:s') . ' UTC';

$cache_dir = sys_get_temp_dir();
$key = $cache_dir . '/track_' . md5($ip . '|' . $path);
$allow = true;
$cooldown = 10; // seconds

if (file_exists($key)) {
    $last = intval(@file_get_contents($key));
    if (time() - $last < $cooldown) {
        $allow = false;
    }
}

$msg = "Site visit\nTime: $time\nIP: $ip\nPath: $path\nUA: $ua";
if ($ref) $msg .= "\nRef: $ref";

if ($allow) {
    @file_put_contents($key, strval(time()));
    $payload = json_encode(['chat_id' => $CHAT_ID, 'text' => $msg], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $url = "https://api.telegram.org/bot" . $BOT_TOKEN . "/sendMessage";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);
}

header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate');
echo hex2bin('47494638396101000100800000ffffff21f90401000001002c00000000010001000002024401003b');
exit;
?>