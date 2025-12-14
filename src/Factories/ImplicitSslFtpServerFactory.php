<?php
namespace Apie\FtpServer\Factories;

use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use React\Socket\SecureConnector;
use React\Socket\SecureServer;
use React\Socket\SocketServer;

class ImplicitSslFtpServerFactory implements ServerFactoryInterface
{
    public function createConnector(): ConnectorInterface
    {
        return new SecureConnector(
            new Connector(),
            null,
            $this->createSslOptions()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function createSslOptions(): array
    {
        // TODO: add config to provide your own certificate.
        $crtFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'server.crt';
        $keyFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'server.key';
        if (!file_exists($crtFile) || !file_exists($keyFile)) {
            $dn = [
                "commonName" => "localhost",
            ];
            $privkey = openssl_pkey_new();
            $cert = openssl_csr_new($dn, $privkey);
            $cert = openssl_csr_sign($cert, null, $privkey, 3650);
            openssl_x509_export($cert, $out);
            file_put_contents($crtFile, $out);
            openssl_pkey_export($privkey, $out);
            file_put_contents($keyFile, $out);
        }
        return [
            'local_cert' => $crtFile,
            'local_pk' => $keyFile,
            'allow_self_signed' => true,
            'verify_peer' => false,
        ];
    }

    public function createServer(int $port): SecureServer
    {
        
        return new SecureServer(
            new SocketServer('0.0.0.0:' . $port),
            null,
            $this->createSslOptions()
        );
    }
}
