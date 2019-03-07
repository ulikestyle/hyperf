<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Process\Event;

use Hyperf\Process\AbstractProcess;

class BeforeProcessHandle
{
    /**
     * @var AbstractProcess
     */
    public $process;

    /**
     * @var int
     */
    public $index;

    public function __construct(AbstractProcess $process, int $index)
    {
        $this->process = $process;
        $this->index = $index;
    }
}
