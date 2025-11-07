<?php

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\RootFolder;
use Apie\Common\ActionDefinitionProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\FtpServer\FtpServerCommand;
use Apie\FtpServer\FtpServerRunner;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require ('../vendor/autoload.php');
} else {
    require ('../../../vendor/autoload.php');
}

$contextBuilderFactory = new ContextBuilderFactory();
$hashmap = new BoundedContextHashmap();
$actionDefinitionProvider = new ActionDefinitionProvider();
$apieContext = $contextBuilderFactory->createGeneralContext([]);

$command = new FtpServerCommand(
    FtpServerRunner::create(),
    new ApieFilesystem(new RootFolder($hashmap, $actionDefinitionProvider, $apieContext)),
    $contextBuilderFactory
);
$command->run(new ArrayInput([]), new ConsoleOutput());