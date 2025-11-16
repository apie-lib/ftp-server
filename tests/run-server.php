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
use Apie\FtpServer\SiteCommands\StoreTestCoverageCommand;
use Apie\Serializer\Serializer;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\NoCodeCoverageDriverAvailableException;
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
        $context = $context->withContext(
            LoginService::class,
            new LoginService(
                $this->boundedContextHashmap,
                $this->actionDefinitionProvider,
                $this->serializer
            )
        );
        try {
            $filter = new Filter();
            $filter->includeFile(__DIR__ . '/../src');
            $coverage = new CodeCoverage(
                (new Selector)->forLineCoverage($filter),
                $filter
            );

            $coverage->start('FTP integration test');
            return $context->registerInstance($coverage);
        } catch (NoCodeCoverageDriverAvailableException) {
            return $context;
        }
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
    FtpServerRunner::create(new StoreTestCoverageCommand()),
    $factory,
    $contextBuilderFactory
);
$command->run(new ArrayInput([]), new ConsoleOutput());
