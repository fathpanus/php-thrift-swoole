<?php
namespace SwooleThrift;

use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\TTransport;

class TSwooleTransport extends TTransport
{
    /** @var  \swoole_server */
    protected $server;
    /** @var  int  */
    protected $netFD;
    /** @var  string receive data */
    protected $data;
    /**
     * Buffer for read data.
     *
     * @var string
     */
    private $rBuf_;

    /**
     * Buffer for queued output data
     *
     * @var string
     */
    private $wBuf_;

    /**
     * Whether to frame reads
     *
     * @var bool
     */
    private $read_;

    /**
     * Whether to frame writes
     *
     * @var bool
     */
    private $write_;

    public function __construct()
    {
        $this->read_ = true;
        $this->write_ = true;
    }

    /**
     * @return \swoole_server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param \swoole_server $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getNetFD()
    {
        return $this->netFD;
    }

    /**
     * @param int $netFD
     */
    public function setNetFD($netFD)
    {
        $this->netFD = $netFD;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    /**
     * Whether this transport is open.
     *
     * @return boolean true if open
     */
    public function isOpen()
    {
        // TODO: Implement isOpen() method.
        return true;
    }

    /**
     * Open the transport for reading/writing
     *
     * @throws TTransportException if cannot open
     */
    public function open()
    {
        // TODO: Implement open() method.
    }

    /**
     * Close the transport.
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Read some data into the array.
     *
     * @param int $len How much to read
     * @return string The data that has been read
     * @throws TTransportException if cannot read any more data
     */
    public function read($len)
    {
        // TODO: Implement read() method.
        if (TStringFuncFactory::create()->strlen($this->rBuf_) === 0) {
            $this->_readFrame();
        }

        // Just return full buff
        if ($len >= TStringFuncFactory::create()->strlen($this->rBuf_)) {
            $out = $this->rBuf_;
            $this->rBuf_ = null;

            return $out;
        }

        // Return TStringFuncFactory::create()->substr
        $out = TStringFuncFactory::create()->substr($this->rBuf_, 0, $len);
        $this->rBuf_ = TStringFuncFactory::create()->substr($this->rBuf_, $len);

        return $out;
    }

    private function _readFrame($len = 4)
    {
        $buf = substr($this->data, 0, $len);
        $val = unpack('N', $buf);
        $sz = $val[1];

        $this->rBuf_ = substr($this->data, $len, $sz);
    }

    /**
     * Writes the given data out.
     *
     * @param string $buf The data to write
     * @throws TTransportException if writing fails
     */
    public function write($buf, $len = null)
    {
        // TODO: Implement write() method.
        if ($len !== null && $len < TStringFuncFactory::create()->strlen($buf)) {
            $buf = TStringFuncFactory::create()->substr($buf, 0, $len);
        }
        $this->wBuf_ .= $buf;
    }

    public function putBack($data)
    {
        if (TStringFuncFactory::create()->strlen($this->rBuf_) === 0) {
            $this->rBuf_ = $data;
        } else {
            $this->rBuf_ = ($data . $this->rBuf_);
        }
    }

    public function flush()
    {
        $out = pack('N', TStringFuncFactory::create()->strlen($this->wBuf_));
        $out .= $this->wBuf_;
        // Note that we clear the internal wBuf_ prior to the underlying write
        // to ensure we're in a sane state (i.e. internal buffer cleaned)
        // if the underlying write throws up an exception
        $this->wBuf_ = '';
        $this->server->send($this->netFD, $out);
    }

}