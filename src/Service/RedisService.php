<?php

namespace App\Service;

use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class RedisService
{
    private CacheInterface $redis;

    private Redis $client;

    public function __construct(CacheInterface $redisAdapter)
    {
        $this->redis = $redisAdapter;

        $this->initializeClient();
    }

    private function initializeClient()
    {
        $this->client = RedisAdapter::createConnection(
            'redis://localhost'
        );
    }

    public function get($key)
    {
        return $this->client->get($key);
    }

    public function set($key, $value): void
    {
        $this->client->set($key, $value);
    }

    public function exists($key): int
    {
        return $this->client->exists($key);
    }


}