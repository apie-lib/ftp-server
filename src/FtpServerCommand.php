<?php
declare(strict_types=1);

namespace Apie\FtpServer;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\ApieFilesystemFactory;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\FtpServer\Transfers\NoTransferSet;
use Apie\FtpServer\Transfers\TransferInterface;
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
    public function __construct(
        private readonly FtpServerRunner $runner,
        private readonly ApieFilesystemFactory $filesystemFactory,
        private readonly ContextBuilderFactory $contextBuilder,
        private readonly string $defaultIpAddress = '127.0.0.1',
        private readonly string $passiveMinPort = '49152',
        private readonly string $passiveMaxPort = '65534',
    ) {
        parent::__construct('apie:ftp-server');
    }

    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host to bind to', $this->defaultIpAddress)
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port to listen on', '2121')
            ->setDescription('Runs a virtual FTP server to link with Apie')
            ->setHelp('Start an APIE FTP server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $host = (string) $input->getOption('host');
        $port = (int) $input->getOption('port');

        $io->title('APIE FTP server');
        $io->listing([
            'Host: ' . $host,
            'Port: ' . $port,
        ]);

        $loop = Loop::get();

        $server = new SocketServer("0.0.0.0:$port", [], $loop);
        $server->on('connection', function (ConnectionInterface $conn) use ($input, $output) {
            $this->handleConnection($conn, $input, $output);
        });

        $loop->run();
        return Command::SUCCESS;
    }

    private function handleConnection(ConnectionInterface $conn, InputInterface $input, OutputInterface $output)
    {
        $conn->write("220 Apie FTP Server Ready\r\n");
        $context = $this->contextBuilder->createGeneralContext([
            'ftp' => true,
            ConnectionInterface::class => $conn,
            ApieFilesystemFactory::class => $this->filesystemFactory,
            FtpConstants::CURRENT_PWD => '/',
            TransferInterface::class => new NoTransferSet(),
            FtpConstants::PUBLIC_IP => $input->getOption('host'),
            FtpConstants::PASV_MIN_PORT => $this->passiveMinPort,
            FtpConstants::PASV_MAX_PORT => $this->passiveMaxPort,
        ]);
        $filesystem = $this->filesystemFactory->create($context);
        $context = $context
            ->withContext(ApieFilesystem::class, $filesystem)
            ->withContext(FtpConstants::CURRENT_FOLDER, $filesystem->rootFolder);

        $conn->on('data', function ($data) use ($conn, $output, &$context) {
            $command = trim($data);

            [$cmd, $arg] = array_pad(explode(' ', $command, 2), 2, null);
            $cmd = strtoupper($cmd);
            $output->writeln("Command $cmd $arg");

            $context = $this->runner->run($context, $cmd, $arg ?? '');
        });
    }
}
