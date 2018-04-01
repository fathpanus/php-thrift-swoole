<?php
namespace SwooleThrift;

use Thrift\Server\TServerTransport;
use Thrift\Transport\TTransport;

class TSwooleServerTransport extends TServerTransport
{
    /** @var  string $host */
    protected $host;
    /** @var  int $port */
    protected $port;
    /** @var  array $setting */
    protected $setting;

    public function __construct($host, $port, $setting = [])
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return array
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * @param array $setting
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }


    /**
     * List for new clients
     *
     * @return void
     */
    public function listen()
    {
        // TODO: Implement listen() method.

    }

    /**
     * Close the server
     *
     * @return void
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Subclasses should use this to implement
     * accept.
     *
     * @return TTransport
     */
    protected function acceptImpl()
    {
        // TODO: Implement acceptImpl() method.
        return new TSwooleTransport();
    }
}