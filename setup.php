<?php

use rtens\dox\Configuration;
use watoki\cfg\cli\CreateUserConfigurationCommand;
use watoki\cli\CliApplication;
use watoki\cli\commands\DependentCommandGroup;
use watoki\cli\commands\GenericCommand;
use watoki\cli\Console;

if (!file_exists('build/composer.phar')) {
    @mkdir('build');
    @mkdir('user');

    echo "Downloading Composer installer..." . PHP_EOL;
    file_put_contents("build/install_composer.php", file_get_contents('http://getcomposer.org/installer'));

    echo "Installing composer.phar" . PHP_EOL;
    system("php build/install_composer.php --install-dir build");
    system("php build/composer.phar install --dev");
}

require_once __DIR__ . "/bootstrap.php";

$commands = new DependentCommandGroup();
$app = new CliApplication($commands);

$commands->add('createUserConfig', new CreateUserConfigurationCommand(Configuration::$CLASS,
    ROOT . '/user/UserConfiguration.php'));

$commands->add('installDependencies', new GenericCommand(function () {
    system("php build/composer.phar install --dev");
}));

$commands->add('updateDependencies', new GenericCommand(function () {
    system("php build/composer.phar update");
}));

$commands->add('test', GenericCommand::build(function () {
    system("php vendor/phpunit/phpunit/phpunit.php --log-tap build/report.tap");
})->setDescription('Runs the test suite.'));

$commands->add('install', new GenericCommand(function (Console $console) {
    if (!file_exists('.htaccess')) {
        $url = $console->ask('Root URL (e.g. /dox): ') ?: '/';

        $console->out->writeLine("Copying .htaccess");
        file_put_contents('.htaccess', str_replace('/dox', $url, file_get_contents('.htaccess.dist')));
    }
}));

$commands->add('build', new GenericCommand());

$commands->addDependency('build', 'installDependencies');
$commands->addDependency('install', 'build');
$commands->addDependency('install', 'createUserConfig');
$commands->addDependency('test', 'build');

$app->run();