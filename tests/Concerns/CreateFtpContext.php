<?php
namespace Apie\Tests\FtpServer\Concerns;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\RootFolder;
use Apie\Common\ActionDefinitionProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Export\CsvExport;
use Apie\Export\EntityExport;
use Apie\Fixtures\BoundedContextFactory;
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
        
        $entityExport = new EntityExport(
            new ColumnSelector(),
            new CsvExport(),
            Serializer::create(),
        );

        $context = new ApieContext(
            [
                ConnectionInterface::class => new FakeConnection(),
                BoundedContextHashmap::class => $hashmap,
                EntityExport::class => $entityExport,
                FtpConstants::CURRENT_PWD => trim($currentPath, '/'),
                TransferInterface::class => new FakeTransfer(),
                'ftp' => true,
            ]
        );

        $filesystem = new ApieFilesystem(
            new RootFolder($hashmap, new ActionDefinitionProvider(), $context),
        );

        return $context
            ->withContext(ApieFilesystem::class, $filesystem)
            ->withContext(FtpConstants::CURRENT_FOLDER, $filesystem->visit($currentPath));
    }
}
