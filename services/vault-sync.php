<?php
$vaultUrl = "http://payshield_vault:8200/v1/secret/data/payshield";
$token = "root";

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "X-Vault-Token: $token\r\n"
    ]
];
$context = stream_context_create($opts);
$response = @file_get_contents($vaultUrl, false, $context);

if ($response === false) {
    die("Lỗi: Không thể gọi Vault!\n");
}

$data = json_decode($response, true);
$secrets = $data['data']['data'];
$envFile = __DIR__ . '/.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

foreach ($secrets as $key => $value) {
    
    $escapedValue = str_replace("\n", "\\n", $value);
    $newLine = "{$key}=\"{$escapedValue}\"";

    if (preg_match("/^{$key}=.*/m", $envContent, $matches)) {
        $envContent = str_replace($matches[0], $newLine, $envContent);
    } else {
        $envContent .= "\n{$newLine}";
    }
}

file_put_contents($envFile, $envContent);
?>