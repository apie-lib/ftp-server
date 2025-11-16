<?php
namespace Apie\FtpServer;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Commands\CdupCommand;
use Apie\FtpServer\Commands\CwdCommand;
use Apie\FtpServer\Commands\ListCommand;
use Apie\FtpServer\Commands\NlstCommand;
use Apie\FtpServer\Commands\PassCommand;
use Apie\FtpServer\Commands\PasvCommand;
use Apie\FtpServer\Commands\PortCommand;
use Apie\FtpServer\Commands\PwdCommand;
use Apie\FtpServer\Commands\QuitCommand;
use Apie\FtpServer\Commands\RetrCommand;
use Apie\FtpServer\Commands\SiteCommand;
use Apie\FtpServer\Commands\SystCommand;
use Apie\FtpServer\Commands\TypeCommand;
use Apie\FtpServer\Commands\UserCommand;
use Apie\FtpServer\Lists\CommandHashmap;
use Apie\FtpServer\SiteCommands\SiteCommandInterface;
use React\Socket\ConnectionInterface;

class FtpServerRunner
{
    public function __construct(
        private readonly CommandHashmap $commands
    ) {
    }

    public static function create(SiteCommandInterface... $siteCommands): self
    {
        return new self(
            new CommandHashmap([
                'CDUP' => new CdupCommand(),
                'CWD' => new CwdCommand(),
                'LIST' => new ListCommand(),
                'NLST' => new NlstCommand(),
                'PASS' => new PassCommand(),
                'PASV' => new PasvCommand(),
                'PORT' => new PortCommand(),
                'PWD'  => new PwdCommand(),
                'QUIT' => new QuitCommand(),
                'RETR' => new RetrCommand(),
                'SITE' => SiteCommand::create(...$siteCommands),
                'SYST' => new SystCommand(),
                'TYPE' => new TypeCommand(),
                'USER' => new UserCommand(),
            ])
        );
    }

    public function run(ApieContext $apieContext, string $command, string $arguments = ''): ApieContext
    {
        if (!isset($this->commands[$command])) {
            $apieContext->getContext(ConnectionInterface::class)->write("502 Command not implemented\r\n");
            error_log("Unknown command " . $command);
            return $apieContext;
        }
        $commandExecutable = $this->commands[$command];
        return $commandExecutable->run($apieContext, $arguments);
    }
}
