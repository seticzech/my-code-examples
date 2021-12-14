<?php

require __DIR__ . '/vendor/autoload.php';

echo Nette\Neon\Neon::encode([
    'parameters' => [
        'users' => [
            'ben' => getenv('BEN_USER_PASSWORD') ?: \Nette\Utils\Random::generate(),
        ]
    ]
]);
