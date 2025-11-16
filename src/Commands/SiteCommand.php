<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\SiteCommands\IdleCommand;
use Apie\FtpServer\SiteCommands\SiteCommandInterface;
use Doctrine\ORM\Mapping\Id;
use React\Socket\ConnectionInterface;

class SiteCommand implements CommandInterface
{
    private array $siteCommands;

    public function __construct(SiteCommandInterface... $siteCommands) {
        $this->siteCommands = [];
        foreach ($siteCommands as $siteCommand) {
            $this->siteCommands[strtoupper($siteCommand->getName())] = $siteCommand;
        }
    }

    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        list($command, $args) = explode(' ', $arg, 2);
        $command = strtoupper($command);
        if ($command === 'HELP') {
            // Iterate all registered SITE commands and print their help text.
            // Use a 211 reply (system status) or 200-style single-line reply. We'll send multiple lines.
            foreach ($this->siteCommands as $name => $siteCmd) {
                // Each site command implements getHelpText()
                $conn->write(sprintf("214-%s %s\r\n", $name, $siteCmd->getHelpText()));
            }
            // End of help list
            $conn->write("214 End of SITE HELP list\r\n");
            return $apieContext;
        }
        if (isset($this->siteCommands[$command])) {
            $siteCommand = $this->siteCommands[$command];
            return $siteCommand->run($apieContext, $args ?? '');
        }
        $conn->write("502 Command not implemented\r\n");
        return $apieContext;
    }

    public static function create(SiteCommandInterface... $siteCommands): self
    {
        $siteCommands[] = new IdleCommand();
        return new self(...$siteCommands);
    }
}
