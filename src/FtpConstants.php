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

    /**
     * PASV command public IP address to report (127.0.0.1 when missing)
     */
    public const PUBLIC_IP = 'ftp_public_ip';

    /**
     * PASV command minimum port number to use
     */
    public const PASV_MIN_PORT = 'ftp_passive_min_port';

    /**
     * PASV command maximum port number to use (inclusive)
     */
    public const PASV_MAX_PORT = 'ftp_passive_max_port';

    /**
     * PORT command ip address to connect to
     */
    public const IP = 'ftp_port_ip';

    /**
     * PORT command port number to connect to
     */
    public const PORT = 'ftp_port_port';
}
