<?php

namespace App\Cache;

use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisClient
{
    private Redis $client;

    public function __construct()
    {
        $this->client = RedisAdapter::createConnection(
            'redis://localhost'
        );
    }

    public function get(string $key): mixed
    {
        return $this->client->get($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->client->set($key, $value, 3600);
    }

    public function exists(string $key): bool
    {
        return (bool) $this->client->exists($key);
    }

    public function clear(string $key): int
    {
        return $this->client->del($key);
    }
}