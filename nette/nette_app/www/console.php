<?php

use Contributte\Console\Application;

/** @var Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

// Get application from DI container.
$application = $container->getByType(Application::class);

// Run application.
exit($application->run());
