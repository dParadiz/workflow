<?php declare(strict_types=1);

namespace Workflow\Step\ActionImplementation;

use Closure;
use Workflow\Context;
use Workflow\Step\Action;

final class Process implements Action
{
    public function __construct(private Closure $procedure)
    {
    }

    public function execute(Context $context): mixed
    {
        return ($this->procedure)($context);
    }
}