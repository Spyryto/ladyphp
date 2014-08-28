#!/usr/bin/env php
<?php
$inputFile = dirname(__DIR__) . '/src/Lady.php';
$outputFile = __DIR__ . '/lady.js';
require($inputFile);
$jsRules = json_encode(Lady::$rules);
$jsRules = preg_replace('{\s+(?![$=])|\\\\n\s+}', '', $jsRules);
$jsCode = explode("\n", file_get_contents($outputFile));
$jsCode[2] = sprintf('Lady.rules = %s;', $jsRules);
file_put_contents($outputFile, implode("\n", $jsCode));
printf("Rules in %s updated.\n", basename($outputFile));
