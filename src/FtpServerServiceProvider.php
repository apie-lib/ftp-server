<?php
namespace Apie\FtpServer;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: ftp.yaml
 * @codeCoverageIgnore
 */
class FtpServerServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\FtpServer\FtpServerCommand::class,
            function ($app) {
                return new \Apie\FtpServer\FtpServerCommand(
                    $app->make(\Apie\FtpServer\FtpServerRunner::class),
                    $app->make(\Apie\ApieFileSystem\ApieFilesystemFactory::class),
                    $app->make(\Apie\Core\ContextBuilders\ContextBuilderFactory::class)
                );
            }
        );
        $this->app->singleton(
            \Apie\FtpServer\FtpServerRunner::class,
            function ($app) {
                return \Apie\FtpServer\FtpServerRunner::create(
                
                );
                
            }
        );
        
    }
}
