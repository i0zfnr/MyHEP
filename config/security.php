<?php

$configuredHosts = array_values(array_filter(array_map(
    static fn (string $host): string => trim($host),
    explode(',', (string) env('ALLOWED_HOSTS', '')),
)));

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST);

return [
    'allowed_hosts' => array_map(
        static fn (string $host): string => '^'.preg_quote($host, '/').'$' ,
        $configuredHosts ?: array_filter([(string) $appHost]),
    ),
];
