<?php

// Test AliExpress signature generation

$apiKey = '517420';
$apiSecret = 'y86kcMc4Yyyima1vDkUSJspmuuMc38iT';

$params = [
    'app_key' => $apiKey,
    'method' => 'aliexpress.ds.recommend.feed.get',
    'timestamp' => '1760872537000',
    'format' => 'json',
    'v' => '2.0',
    'sign_method' => 'sha256',
    'page_no' => '1',
    'page_size' => '20',
    'target_currency' => 'USD',
    'target_language' => 'EN',
    'ship_to_country' => 'EG',
    'sort' => 'SALE_PRICE_ASC',
    'keywords' => 'phone'
];

// Sort
ksort($params);

echo "=== Testing Different Signature Methods ===\n\n";

// Method 1: Just key+value pairs
$str1 = '';
foreach ($params as $key => $value) {
    $str1 .= $key . $value;
}
$sign1 = strtoupper(hash_hmac('sha256', $str1, $apiSecret));
echo "Method 1 (key+value): \n";
echo "String: " . $str1 . "\n";
echo "Signature: " . $sign1 . "\n\n";

// Method 2: /rest prefix + key+value
$str2 = '/rest';
foreach ($params as $key => $value) {
    $str2 .= $key . $value;
}
$sign2 = strtoupper(hash_hmac('sha256', $str2, $apiSecret));
echo "Method 2 (/rest + key+value): \n";
echo "String: " . $str2 . "\n";
echo "Signature: " . $sign2 . "\n\n";

// Method 3: secret + key+value + secret (old style)
$str3 = $apiSecret;
foreach ($params as $key => $value) {
    $str3 .= $key . $value;
}
$str3 .= $apiSecret;
$sign3 = strtoupper(hash('sha256', $str3));
echo "Method 3 (secret+key+value+secret with SHA256): \n";
echo "String length: " . strlen($str3) . "\n";
echo "Signature: " . $sign3 . "\n\n";

// Method 4: API path only
$str4 = '';
foreach ($params as $key => $value) {
    if ($value !== '' && $value !== null) {
        $str4 .= $key . $value;
    }
}
$sign4 = strtoupper(hash_hmac('sha256', $str4, $apiSecret));
echo "Method 4 (key+value with HMAC, skip empty): \n";
echo "String: " . $str4 . "\n";
echo "Signature: " . $sign4 . "\n\n";
