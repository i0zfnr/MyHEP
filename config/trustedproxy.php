<?php

$configuredProxies = array_values(array_filter(array_map(
    static fn (string $proxy): string => trim($proxy),
    explode(',', (string) env('TRUSTED_PROXIES', '')),
)));

return [
    'proxies' => $configuredProxies ?: null,
];
