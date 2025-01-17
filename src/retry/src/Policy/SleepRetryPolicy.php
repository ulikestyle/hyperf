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
namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryContext;

class SleepRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var int
     */
    private $base;

    /**
     * @var string
     */
    private $sleepStrategyClass;

    public function __construct(int $base, string $sleepStrategyClass)
    {
        $this->base = $base;
        $this->sleepStrategyClass = $sleepStrategyClass;
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        return true;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        $retryContext['strategy']->sleep();
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $parentRetryContext['strategy'] = make($this->sleepStrategyClass, [
            'base' => $this->base,
        ]);
        return $parentRetryContext;
    }
}
