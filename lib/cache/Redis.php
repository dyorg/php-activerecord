<?php
namespace ActiveRecord;

/**
 * Class Redis
 * @package ActiveRecord
 */
class Redis
{
    const DEFAULT_PORT = 6379;

    /** @var \Predis\Client */
    private $adapter;

    /**
     * Creates a \Predis\Client instance.
     *
     * Takes an $options array w/ the following parameters:
     *
     * <ul>
     * <li><b>host:</b> host for the Redis server </li>
     * <li><b>port:</b> port for the Redis server </li>
     * </ul>
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $connection = array(
            'host'      => $options['host'],
            'port'      => isset($options['port']) ? $options['port'] : self::DEFAULT_PORT,
            'timeout'   => 0,
            'async'     => false,
        );

        if (isset($options['pass'])) {
            $connection['password'] = $options['pass'];
        }

        $this->adapter = new \Predis\Client($connection);

        if (!$this->adapter)
        {
            throw new CacheException("Could not connect to $options[host]:$options[port]");
        }
    }

    /**
     *
     */
    public function flush()
    {
        $this->adapter->flushdb();
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function read($key)
    {
        $data = $this->adapter->get($key);

        return $data ? unserialize($data) : null;
    }

    /**
     * @param $key
     * @param $value
     * @param $expire
     */
    public function write($key, $value, $expire)
    {
        $this->adapter->setex($key, $expire, serialize($value));
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->adapter->del($key);
    }
}