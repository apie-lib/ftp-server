<?php
namespace Apie\FtpServer;

use Apie\Core\ApieLib;
use Apie\FtpServer\Enums\PortStatus;
use React\Socket\SocketServer;
use Throwable;

class PassivePortManager
{
    /**
     * @var array<int, SocketServer> $usedPorts
     */
    private static array $usedPorts = [];

    /**
     * @var array<int, int> $errorPorts
     */
    private static array $errorPorts = [];

    /**
     * @var array<int, SocketServer> $releasedServers
     */
    private static array $releasedServers = [];

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function getAvailablePort(int $minPort, int $maxPort): SocketServer
    {
        /** @vary array<int, PortStatus> $portStatuses */
        $portStatuses = [];
        $ports = range($minPort, $maxPort);
        // shuffle($ports); // shuffly ports to avoid security issues
        foreach ($ports as $port) {
            if (isset(self::$usedPorts[$port])) {
                $portStatuses[$port] = PortStatus::InUse;
                continue;
            }
            if ((self::$errorPorts[$port] ?? 0) > ApieLib::getPsrClock()->now()->getTimestamp()) {
                $portStatuses[$port] = PortStatus::Error;
                continue;
            }
            if (isset(self::$errorPorts[$port])) {
                unset(self::$errorPorts[$port]);
            }
            if (isset(self::$releasedServers[$port])) {
                $portStatuses[$port] = PortStatus::Available;
                $server = self::$releasedServers[$port];
                unset(self::$releasedServers[$port]);
                self::$usedPorts[$port] = $server;
                $server->on('close', function () use ($port) {
                    if (isset(self::$usedPorts[$port])) {
                        unset(self::$usedPorts[$port]);
                    }
                    if (isset(self::$releasedServers[$port])) {
                        unset(self::$releasedServers[$port]);
                    }
                });
                $server->on('error', function () use ($port) {
                    self::$errorPorts[$port] = ApieLib::getPsrClock()->now()->getTimestamp() + 60;
                });var_dump($portStatuses);
                return $server;
            }
            try {
                $portStatuses[$port] = PortStatus::Available;
                var_dump($portStatuses);
                self::$usedPorts[$port] = new SocketServer('0.0.0.0:' . $port);
                return self::$usedPorts[$port];
            } catch (Throwable) {
                $portStatuses[$port] = PortStatus::Error;
                var_dump($portStatuses);
                self::$errorPorts[$port] = ApieLib::getPsrClock()->now()->getTimestamp() + 60;
                continue;
            }
        }
        $suffix = '';
        if (count($portStatuses) < 30) {
            $suffix = ' Port status: ' . json_encode($portStatuses);
        }
        throw new \RuntimeException("No available passive ports in range $minPort-$maxPort$suffix");
    }

    public static function release(SocketServer $server): void
    {
        $address = $server->getAddress();
        if ($address === null) {
            return;
        }
        $parts = \explode(':', $address);
        $port = (int) \array_pop($parts);
        self::$releasedServers[$port] = $server;
        unset(self::$usedPorts[$port]);
    }
}