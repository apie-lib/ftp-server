<?php
namespace Apie\FtpServer;

final class FtpConstants
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public const USERNAME = 'ftp_username';

    public const CURRENT_PWD = 'ftp_cwd';

    public const CURRENT_FOLDER = 'ftp_current_folder';

    public const IP = 'ftp_port_ip';

    public const PORT = 'ftp_port_port';
}
