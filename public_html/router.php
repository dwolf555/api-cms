<?php

if (!file_exists(__DIR__ . explode('?', $_SERVER['REQUEST_URI'])[0])) {
    require_once __DIR__ . '/index.php';
    return true;
}
$customMimeTypes = [
    'eot' => 'application/vnd.ms-fontobject',
    'svg' => 'image/svg+xml',
    'ttf' => 'application/font-sfnt',
    'woff' => 'application/x-font-woff',
    'woff2' => 'application/font-woff2',
    'json' => 'application/json',
    'xml' => 'application/xml'
];
$fileExtension = pathinfo($_SERVER['SCRIPT_FILENAME'])['extension'];
if (array_key_exists($fileExtension, $customMimeTypes)) {
    header('Content-Type: ' . $customMimeTypes[$fileExtension]);
    readfile($_SERVER["SCRIPT_FILENAME"]);
} else {
    return false;
}

