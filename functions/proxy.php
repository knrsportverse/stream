<?php
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Function to fetch remote content
function fetchUrl($url, $referer, $returnBody = true)
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_REFERER => $referer,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        CURLOPT_HEADER => !$returnBody
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }

    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        "body" => $result,
        "contentType" => $contentType,
        "status" => $httpCode
    ];
}

// =======================
// Handle TS chunk request
// =======================
if (isset($_GET['chunk_url']) && isset($_GET['ref'])) {

    $chunkUrl = base64_decode($_GET['chunk_url']);
    $referer = base64_decode($_GET['ref']);

    $result = fetchUrl($chunkUrl, $referer);

    if (!$result) {
        http_response_code(500);
        exit("Failed to fetch chunk.");
    }

    http_response_code($result['status']);

    if (!empty($result['contentType'])) {
        header("Content-Type: ".$result['contentType']);
    }

    echo $result['body'];
    exit;
}

// =======================
// Handle Playlist Request
// =======================
if (!isset($_GET['token'])) {
    http_response_code(400);
    exit("No token provided");
}

$data = json_decode(base64_decode($_GET['token']), true);

if (!$data) {
    http_response_code(500);
    exit("Invalid token");
}

$m3u8 = $data['m3u8'];
$referer = $data['referer'];

$result = fetchUrl($m3u8, $referer);

if (!$result) {
    http_response_code(500);
    exit("Unable to fetch playlist");
}

$text = $result['body'];

$baseUrl = substr($m3u8, 0, strrpos($m3u8, '/') + 1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https" : "http";

$myProxy = $scheme . "://" .
           $_SERVER['HTTP_HOST'] .
           $_SERVER['PHP_SELF'];

$output = "";

foreach (explode("\n", $text) as $line) {

    $line = trim($line);

    if ($line == "" || str_starts_with($line, "#")) {
        $output .= $line . "\n";
        continue;
    }

    $absolute = str_starts_with($line, "http")
        ? $line
        : $baseUrl . $line;

    $output .= $myProxy
        . "?chunk_url=" . urlencode(base64_encode($absolute))
        . "&ref=" . urlencode(base64_encode($referer))
        . "\n";
}

header("Content-Type: application/vnd.apple.mpegurl");

echo $output;
?>
