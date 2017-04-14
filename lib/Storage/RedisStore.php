<?php

namespace Cart\Storage;

class RedisStore implements Store
{
    private $redis = NULL;
    private $server = '127.0.0.1';
    private $port = 6379;
    private $expire = 0; // 0 = never, in seconds


    /**
     * Get redis instance.
     *
     * @param string $server
     * @param int    $port
     *
     * @return string
     */
    function __construct($server = '127.0.0.1', $port = 6379, $expire = 0)
    {
        $this->redis = new \Redis();

        if($server) {
            $this->server = $server;
        }
        if($port) {
            $this->port = $port;
        }
        if($expire) {
            $this->expire = $expire;
        }
        $this->redis->connect($this->server, $this->port);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        return $this->redis->get($cartId) ? $this->redis->get($cartId) : serialize([]);
    }

    /**
     * {@inheritdoc}
     */
    public function put($cartId, $data)
    {
        $this->redis->set($cartId, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($cartId)
    {
        $this->redis->delete($cartId);
    }

    /**
     * set redis server.
     *
     * @param string $server
     *
     * @return string
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * set redis port.
     *
     * @param string $server
     *
     * @return string
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * set redis expire.
     *
     * @param int $expire
     *
     * @return string
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }
}
