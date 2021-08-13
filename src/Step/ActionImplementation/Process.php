<?php

namespace Workflow\Step\ActionImplementation;

use Closure;
use Workflow\Context;
use Workflow\Step\Action;

class Process implements Action
{
    public function __construct(private Closure $procedure)
    {
    }

    public function execute(Context $context): mixed
    {
        $procedure = $this->procedure;
        return $procedure($context);

    }
}