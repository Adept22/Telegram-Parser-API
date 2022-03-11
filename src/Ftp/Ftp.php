<?php

namespace App\Ftp;

use FtpClient\FtpClient;

class Ftp extends FtpClient
{
    public function connect_url(string $url)
    {
        $parts = parse_url($url);
        $params = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $params);
        }

        $this->connect(
            $parts['host'], 
            $parts['scheme'] === 'ftps' ? true : false, 
            isset($parts['port']) ? (int) $parts['port'] : 21, 
            isset($params['timeout']) ? (int) $params['timeout'] : 90
        );

        $this->login($parts['user'], $parts['pass']);

        $this->chdir($parts['path']);

        if (isset($params['pasv'])) {
            $this->pasv((bool) $params['pasv']);
        }
    }

    /**
     * @param $remoteFile
     * @param $mode
     * @param $resumepos
     *
     * @return resource|null
     */
    public function getStream($remoteFile, $mode = FTP_BINARY, $resumepos = 0)
    {
        $handle = fopen('php://temp', 'r+');

        if ($this->ftp->fget($handle, $remoteFile, $mode, $resumepos)) {
            rewind($handle);

            return $handle;
        }

        return null;
    }
}
