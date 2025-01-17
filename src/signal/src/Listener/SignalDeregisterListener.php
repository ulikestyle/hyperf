<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Signal\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerExit;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Signal\SignalManager;
use Psr\Container\ContainerInterface;

class SignalDeregisterListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            OnWorkerExit::class,
            AfterProcessHandle::class,
            CoroutineServerStop::class,
        ];
    }

    public function process(object $event)
    {
        $manager = $this->container->get(SignalManager::class);
        $manager->setStoped(true);
    }
}
