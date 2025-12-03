<?php
namespace Apie\Tests\FtpServer\Concerns;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\RootFolder;
use Apie\Common\ActionDefinitionProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\InMemory\InMemoryDatalayer;
use Apie\Core\Datalayers\Search\LazyLoadedListFilterer;
use Apie\Core\Indexing\Indexer;
use Apie\Export\CsvExport;
use Apie\Export\EntityExport;
use Apie\Fixtures\BoundedContextFactory;
use Apie\FtpServer\Factories\MockFactory;
use Apie\FtpServer\Factories\ServerFactoryInterface;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\Transfers\TransferInterface;
use Apie\HtmlBuilders\Columns\ColumnSelector;
use Apie\Serializer\Serializer;
use Apie\Tests\FtpServer\FakeConnection;
use Apie\Tests\FtpServer\FakeTransfer;
use React\Socket\ConnectionInterface;

trait CreateFtpContext
{
    private function createContext(string $currentPath): ApieContext
    {
        $hashmap = BoundedContextFactory::createHashmapWithMultipleContexts();
        
        $serializer = Serializer::create();

        $actionDefinitionProvider = new ActionDefinitionProvider();

        $entityExport = new EntityExport(
            new ColumnSelector(),
            new CsvExport(),
            $serializer,
        );

        $context = new ApieContext(
            [
                ConnectionInterface::class => new FakeConnection(),
                BoundedContextHashmap::class => $hashmap,
                ServerFactoryInterface::class => new MockFactory(),
                EntityExport::class => $entityExport,
                FtpConstants::CURRENT_PWD => trim($currentPath, '/'),
                TransferInterface::class => new FakeTransfer(),
                Serializer::class => $serializer,
                ActionDefinitionProvider::class => $actionDefinitionProvider,
                ApieDatalayer::class => new InMemoryDatalayer(
                    new BoundedContextId('default'),
                    new LazyLoadedListFilterer(Indexer::create())
                ),
                'ftp' => true,
            ]
        );

        $filesystem = new ApieFilesystem(
            new RootFolder($hashmap, $actionDefinitionProvider, $context),
        );

        return $context
            ->withContext(ApieFilesystem::class, $filesystem)
            ->withContext(FtpConstants::CURRENT_FOLDER, $filesystem->visit($currentPath));
    }
}
