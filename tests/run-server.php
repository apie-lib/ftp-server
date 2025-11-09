<?php

use Apie\ApieFileSystem\ApieFilesystemFactory;
use Apie\Common\ActionDefinitionProvider;
use Apie\Common\LoginService;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Fixtures\BoundedContextFactory;
use Apie\FtpServer\FtpServerCommand;
use Apie\FtpServer\FtpServerRunner;
use Apie\Serializer\Serializer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require('../vendor/autoload.php');
} else {
    require('../../../vendor/autoload.php');
}

class AddLoginService implements ContextBuilderInterface
{
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly ActionDefinitionProvider $actionDefinitionProvider,
        private readonly Serializer $serializer
    ) {
    }

    public function process(ApieContext $context): ApieContext
    {
        return $context->withContext(
            LoginService::class,
            new LoginService(
                $this->boundedContextHashmap,
                $this->actionDefinitionProvider,
                $this->serializer
            )
        );
    }
}

$hashmap = BoundedContextFactory::createHashmapWithMultipleContexts();
$actionDefinitionProvider = new ActionDefinitionProvider();
$contextBuilderFactory = new ContextBuilderFactory(
    new AddLoginService(
        $hashmap,
        $actionDefinitionProvider,
        Serializer::create()
    )
);
$factory = new ApieFilesystemFactory(
    $actionDefinitionProvider,
    $hashmap
);

$command = new FtpServerCommand(
    FtpServerRunner::create(),
    $factory,
    $contextBuilderFactory
);
$command->run(new ArrayInput([]), new ConsoleOutput());
