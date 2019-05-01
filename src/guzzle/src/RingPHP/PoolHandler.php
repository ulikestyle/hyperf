<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Guzzle\RingPHP;

use GuzzleHttp\Ring\Core;
use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

class PoolHandler extends CoroutineHandler
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    public function __construct(PoolFactory $factory, array $option = [])
    {
        $this->factory = $factory;

        parent::__construct($option);
    }

    public function __invoke($request)
    {
        $method = $request['http_method'] ?? 'GET';
        $scheme = $request['scheme'] ?? 'http';
        $ssl = $scheme === 'https';
        $uri = $request['uri'] ?? '/';
        $body = $request['body'] ?? '';
        $effectiveUrl = Core::url($request);
        $params = parse_url($effectiveUrl);
        $host = $params['host'];
        if (! isset($params['port'])) {
            $params['port'] = $ssl ? 443 : 80;
        }
        $port = $params['port'];
        $path = $params['path'] ?? '/';
        if (isset($params['query']) && is_string($params['query'])) {
            $path .= '?' . $params['query'];
        }

        $pool = $this->factory->get($this->getPoolName($uri), function () use ($host, $port, $ssl) {
            return new Client($host, $port, $ssl);
        }, $this->option);

        $connection = $pool->get();

        $client = $connection->getConnection();
        $client->setMethod($method);
        $client->setData($body);

        // 初始化Headers
        $this->initHeaders($client, $request);
        $settings = $this->getSettings($this->options);

        // 设置客户端参数
        if (! empty($settings)) {
            $client->set($settings);
        }

        $btime = microtime(true);
        $client->execute($path);

        $ex = $this->checkStatusCode($client, $request);
        if ($ex !== true) {
            $connection->close();
            $connection->release();
            return [
                'status' => null,
                'reason' => null,
                'headers' => [],
                'error' => $ex,
            ];
        }

        $response = $this->getResponse($client, $btime, $effectiveUrl);
        $connection->release();

        return $response;
    }

    protected function getPoolName($host, $port, $scheme)
    {
        return sprintf('guzzle.handler.%s.%d.%s', $host, $port, $scheme);
    }
}