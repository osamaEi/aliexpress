<?php

// Test different signature generation methods for AliExpress API

$apiKey = '517420';
$apiSecret = 'y86kcMc4Yyyima1vDkUSJspmuuMc38iT';
$timestamp = '1760873391000';

$params = [
    'app_key' => $apiKey,
    'format' => 'json',
    'method' => 'aliexpress.ds.product.get',
    'product_id' => '1005006340579394',
    'ship_to_country' => 'EG',
    'sign_method' => 'sha256',
    'target_currency' => 'USD',
    'target_language' => 'EN',
    'timestamp' => $timestamp,
    'v' => '2.0',
];

ksort($params);

echo "=== Testing Different Signature Generation Methods ===\n\n";

// Method 1: Current implementation (HMAC-SHA256 with key+value)
$str1 = '';
foreach ($params as $key => $value) {
    $str1 .= $key . $value;
}
$sign1 = strtoupper(hash_hmac('sha256', $str1, $apiSecret));
echo "Method 1 - HMAC-SHA256 (key+value):\n";
echo "String: {$str1}\n";
echo "Signature: {$sign1}\n\n";

// Method 2: MD5 with secret wrapping (old Taobao style)
$str2 = $apiSecret;
foreach ($params as $key => $value) {
    $str2 .= $key . $value;
}
$str2 .= $apiSecret;
$sign2 = strtoupper(md5($str2));
echo "Method 2 - MD5 (secret+key+value+secret):\n";
echo "String length: " . strlen($str2) . "\n";
echo "Signature: {$sign2}\n\n";

// Method 3: HMAC-MD5
$str3 = '';
foreach ($params as $key => $value) {
    $str3 .= $key . $value;
}
$sign3 = strtoupper(hash_hmac('md5', $str3, $apiSecret));
echo "Method 3 - HMAC-MD5 (key+value):\n";
echo "String: {$str3}\n";
echo "Signature: {$sign3}\n\n";

// Method 4: HMAC-SHA256 with secret& (ampersand)
$str4 = '';
foreach ($params as $key => $value) {
    $str4 .= $key . $value;
}
$sign4 = strtoupper(hash_hmac('sha256', $str4, $apiSecret . '&'));
echo "Method 4 - HMAC-SHA256 with secret& (key+value):\n";
echo "String: {$str4}\n";
echo "Secret: {$apiSecret}&\n";
echo "Signature: {$sign4}\n\n";

// Method 5: Plain SHA256 with secret wrapping
$str5 = $apiSecret;
foreach ($params as $key => $value) {
    $str5 .= $key . $value;
}
$str5 .= $apiSecret;
$sign5 = strtoupper(hash('sha256', $str5));
echo "Method 5 - SHA256 (secret+key+value+secret):\n";
echo "String length: " . strlen($str5) . "\n";
echo "Signature: {$sign5}\n\n";

// Method 6: Binary HMAC-SHA256, then hex
$str6 = '';
foreach ($params as $key => $value) {
    $str6 .= $key . $value;
}
$sign6 = strtoupper(bin2hex(hash_hmac('sha256', $str6, $apiSecret, true)));
echo "Method 6 - Binary HMAC-SHA256 to hex (key+value):\n";
echo "String: {$str6}\n";
echo "Signature: {$sign6}\n\n";
