<?php

$config = require __DIR__.'/backup.php';

$config['backup']['name'] = 'Database';
$config['backup']['source']['files']['include'] = [];

return $config;
