<?php

namespace Cart\Storage;

class MemcacheStore implements Store
{
    private $mc = NULL;
    private $server = 'localhost';
    private $port = 11211;
    private $expire = 0;


    /**
     * contruct instance.
     *
     * @param string $server
     * @param int    $port
     *
     * @return string
     */
    function __construct($server = 'localhost', $port = 11211)
    {
        $this->mc = new \Memcache();

        if($server) {
            $this->server = $server;
        }
        if($port) {
            $this->port = $port;
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
        $this->getInstance()->delete($cartId);
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
