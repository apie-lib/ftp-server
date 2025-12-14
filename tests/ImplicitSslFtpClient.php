<?php
namespace Apie\Tests\FtpServer;

use CurlHandle;
use SensitiveParameter;

class ImplicitSslFtpClient
{

    private CurlHandle $curlHandle;

    private string $url;

    public function __construct(
        string $username,
        #[SensitiveParameter] string $password,
        string $server,
        int $port = 990,
        string $initialPath = '',
        bool $passiveMode = false
    ) {
        if (! $username) {
            throw new \Exception('FTP Username is blank.');
        }


        if (! $server) {
            throw new \Exception('FTP Server is blank.');
        }


        $this->url = 'ftps://' . $server . '/' . $initialPath;

        $this->curlHandle = curl_init();

        if (! $this->curlHandle) {
            throw new \Exception('Could not initialize cURL.');
        }

        // connection options
        $options = array(
            CURLOPT_USERPWD        => $username . ':' . $password,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // require SSL For both control and data connections
            CURLOPT_USE_SSL        => CURLFTPSSL_TRY,
            CURLOPT_FTPSSLAUTH     => CURLFTPAUTH_DEFAULT,
            CURLOPT_UPLOAD         => true,
            CURLOPT_PORT           => $port,
            CURLOPT_TIMEOUT        => 30,
        );

        // cURL FTP enables passive mode by default, so disable it
        if (! $passiveMode) {
            $options[ CURLOPT_FTPPORT ] = '-';
        }

        // set connection options, use foreach so useful errors can be caught instead of a generic "cannot set options" error with curl_setopt_array()
        foreach ($options as $option_name => $option_value) {

            if (! curl_setopt($this->curlHandle, $option_name, $option_value)) {
                throw new \Exception(sprintf('Could not set cURL option: %s', $option_name));
            }
        }

    }

    public function upload(string $remoteFile, string $localFile)
    {

        if (! curl_setopt($this->curlHandle, CURLOPT_URL, $this->url . $remoteFile)) {
            throw new \Exception("Could not set cURL file name: $remoteFile");
        }

        $stream = fopen('php://temp', 'w+');

        if (! $stream) {
            throw new \Exception('Could not open php://temp for writing.');
        }
        fwrite($stream, $localFile);

        rewind($stream);

        if (! curl_setopt($this->curlHandle, CURLOPT_INFILE, $stream)) {
            throw new \Exception("Could not load file $localFile");
        }

        if (! curl_exec($this->curlHandle)) {
            throw new \Exception(sprintf('Could not upload file. cURL Error: [%s] - %s', curl_errno($this->curl_handle), curl_error($this->curl_handle)));
        }

        fclose($stream);
    }

    
    public function download(string $remoteFile, string $localFile)
    {
        if (!curl_setopt($this->curlHandle, CURLOPT_URL, $this->url . $remoteFile)) {
            throw new \Exception("Could not set cURL file name: $remoteFile");
        }

        $fp = fopen($localFile, 'w');
        if (!$fp) {
            throw new \Exception("Could not open $localFile for writing.");
        }

        if (!curl_setopt($this->curlHandle, CURLOPT_FILE, $fp)) {
            fclose($fp);
            throw new \Exception("Could not set cURL output file: $localFile");
        }

        // Set to download mode
        curl_setopt($this->curlHandle, CURLOPT_UPLOAD, false);

        if (!curl_exec($this->curlHandle)) {
            $err = sprintf('Could not download file. cURL Error: [%s] - %s', curl_errno($this->curlHandle), curl_error($this->curlHandle));
            fclose($fp);
            throw new \Exception($err);
        }

        fclose($fp);
    }
    /**
     * List files in a remote directory.
     *
     * @param string $remoteDir The remote directory path (relative to initialPath)
     * @return array List of file and directory names
     * @throws \Exception on failure
     */
    public function listContents(string $remoteDir = ''): array
    {
        $listUrl = $this->url . ltrim($remoteDir, '/');
        if (!curl_setopt($this->curlHandle, CURLOPT_URL, $listUrl)) {
            throw new \Exception("Could not set cURL directory: $remoteDir");
        }

        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_UPLOAD, false);

        $result = curl_exec($this->curlHandle);
        if ($result === false) {
            throw new \Exception(sprintf('Could not list files. cURL Error: [%s] - %s', curl_errno($this->curlHandle), curl_error($this->curlHandle)));
        }

        $lines = explode("\n", $result);
        $fileNames = array_filter(array_map(function ($line) {
            $parts = array_filter(preg_split('/\s+/', trim($line)));
            return end($parts);
        }, $lines));
        return $fileNames;
    }
}
