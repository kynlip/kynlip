<?php
// proxy.php

// CORS và MIME
header('Access-Control-Allow-Origin: *');

$url = $_GET['url'] ?? '';
$segment = $_GET['segment'] ?? null;
$base = $_GET['base'] ?? null;

// Nếu đang xử lý segment .ts
if ($segment && $base) {
    $tsUrl = rtrim($base, '/') . '/' . ltrim($segment, '/');

    header('Content-Type: video/MP2T');
    header('Accept-Ranges: bytes');

    $ch = curl_init($tsUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_BUFFERSIZE     => 8192,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0"
        ],
        CURLOPT_WRITEFUNCTION  => function($ch, $chunk) {
            echo $chunk;
            flush();
            return strlen($chunk);
        }
    ]);
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// Nếu đang xử lý playlist .m3u8
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('#ERROR: Missing or invalid m3u8 URL');
}

// Helper tải file .m3u8
function fetchM3U8($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0"
        ]
    ]);
    $data = curl_exec($ch);
    if ($data === false) {
        http_response_code(502);
        exit('#ERROR: cURL: ' . curl_error($ch));
    }
    curl_close($ch);
    return $data;
}

// 1. Tải m3u8 đầu tiên
$m3u8Content = fetchM3U8($url);

// 2. Nếu là master playlist → lấy m3u8 con
if (preg_match('#EXT-X-STREAM-INF#', $m3u8Content) && preg_match('#([^\r\n]+\.m3u8)#', $m3u8Content, $m)) {
    $subPath = trim($m[1]);
    $parsedUrl = parse_url($url);
    $baseUrl = dirname($parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path']);
    $url = rtrim($baseUrl, '/') . '/' . ltrim($subPath, '/');
    $m3u8Content = fetchM3U8($url);
}

// 3. Lọc bỏ quảng cáo giữa 2 DISCONTINUITY
$lines = explode("\n", $m3u8Content);
$filtered = [];
$skip = false;
foreach ($lines as $line) {
    if (strpos($line, '#EXT-X-DISCONTINUITY') !== false) {
        $skip = !$skip;
        continue;
    }
    if ($skip) continue;
    $filtered[] = $line;
}

// 4. Rewrite .ts về proxy
$parsed = parse_url($url);
$tsBase = dirname($parsed['scheme'] . '://' . $parsed['host'] . $parsed['path']);
$self = ($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http';
$self .= '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/proxy.php';

foreach ($filtered as &$line) {
    if (preg_match('/\.ts$/', $line)) {
        $line = $self . '?segment=' . urlencode($line) . '&base=' . urlencode($tsBase . '/');
    }
}
unset($line);

// 5. Trả m3u8 đã xử lý
header('Content-Type: application/vnd.apple.mpegurl');
echo implode("\n", $filtered);
