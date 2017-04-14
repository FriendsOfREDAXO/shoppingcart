<?php

namespace Cart\Storage;

class MemcachedStore implements Store
{
    private $mc = NULL;
    private $server = 'localhost';
    private $port = 11211;
    private $expire = 0;


    /**
     * Get memcached instance.
     *
     * @param string $server
     * @param int    $port
     *
     * @return string
     */
    function __construct($server = 'localhost', $port = 11211, $expire = 0)
    {
        $this->mc = new \Memcached();

        if($server) {
            $this->server = $server;
        }
        if($port) {
            $this->port = $port;
        }
        if($expire) {
            $this->expire = $expire;
        }
        $this->mc->addServer($this->server, $this->port);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        return $this->mc->get($cartId) ? $this->mc->get($cartId) : serialize([]);
    }

    /**
     * {@inheritdoc}
     */
    public function put($cartId, $data)
    {
        $this->mc->set($cartId, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($cartId)
    {
        $this->mc->delete($cartId);
    }

    /**
     * set memcached server.
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
     * set memcached port.
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
     * set memcached expire.
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
