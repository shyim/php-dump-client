<?php

exec('rm -rf build');
exec('php php-scoper.phar add-prefix');
exec('composer dump-autoload -d build -o');
exec('rm build/vendor/composer/InstalledVersions.php');

// Patch composer.json

$composerJson = json_decode(file_get_contents('build/composer.json'), true);
$composerJson['name'] = 'shyim/php-dump-client-prefixed';

foreach ($composerJson['require'] as $key => $val) {
    if (strpos($key, 'ext-') === false && $key !== 'php') {
        unset($composerJson['require'][$key]);
    }
}

file_put_contents('build/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
