<?php
namespace Apie\FtpServer;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Commands\ListCommand;
use Apie\FtpServer\Commands\NlstCommand;
use Apie\FtpServer\Commands\PassCommand;
use Apie\FtpServer\Commands\PortCommand;
use Apie\FtpServer\Commands\PwdCommand;
use Apie\FtpServer\Commands\QuitCommand;
use Apie\FtpServer\Commands\RetrCommand;
use Apie\FtpServer\Commands\TypeCommand;
use Apie\FtpServer\Commands\UserCommand;
use Apie\FtpServer\Lists\CommandHashmap;
use React\Socket\ConnectionInterface;

class FtpServerRunner
{
    public function __construct(
        private readonly CommandHashmap $commands
    ) {
    }

    public static function create(): self
    {
        return new self(
            new CommandHashmap([
                'USER' => new UserCommand(),
                'PASS' => new PassCommand(),
                'PWD' => new PwdCommand(),
                'LIST' => new ListCommand(),
                'RETR' => new RetrCommand(),
                'QUIT' => new QuitCommand(),
                'TYPE' => new TypeCommand(),
                'PORT' => new PortCommand(),
                'NLST' => new NlstCommand(),
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