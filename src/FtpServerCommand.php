<?php
declare(strict_types=1);

namespace Apie\FtpServer;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\FtpServer\FtpServerRunner;
use React\EventLoop\Factory;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FtpServerCommand extends Command
{
    protected static $defaultName = 'apie:ftp:serve';
    protected static $defaultDescription = 'Run a lightweight FTP server';

    public function __construct(
        private readonly FtpServerRunner $runner,
        private readonly ApieFilesystem $filesystem,
        private readonly ContextBuilderFactory $contextBuilder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host to bind to', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port to listen on', '2121')
            ->setHelp('Start an APIE FTP server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $host = (string) $input->getOption('host');
        $port = (int) $input->getOption('port');

        $io->title('FTP Server (skeleton)');
        $io->listing([
            'Host: ' . $host,
            'Port: ' . $port,
        ]);

        $loop = Loop::get();

        $server = new SocketServer("0.0.0.0:$port", [], $loop);
        $server->on('connection', function (ConnectionInterface $conn) {
            $this->handleConnection($conn);
        });

        $io->warning('FTP server functionality is not implemented. This command is a skeleton.');
        $loop->run();
        return Command::SUCCESS;
    }

    private function handleConnection(ConnectionInterface $conn)
    {
        $conn->write("220 PHP Virtual FTP Server Ready\r\n");
        $context = $this->contextBuilder->createGeneralContext([
            'ftp' => true,
            ConnectionInterface::class => $conn,
            ApieFilesystem::class => $this->filesystem,
            'ftp_current_folder' => $this->filesystem->rootFolder,
            'ftp_cwd' => '/',
        ]);

        $conn->on('data', function ($data) use ($conn, &$context) {
            $command = trim($data);
            //echo "⇢ $command\n";

            [$cmd, $arg] = array_pad(explode(' ', $command, 2), 2, null);
            $cmd = strtoupper($cmd);

            $context = $this->runner->run($context, $cmd, $arg ?? '');
        });
    }
}