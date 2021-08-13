<?php

namespace Workflow\Step\ExitActionImplementation;

use Workflow\Context;
use Workflow\Step\ExitAction;

class Next implements ExitAction
{
    public function __construct(private string $nextStepName = '')
    {
    }

    public function execute(Context $context): string
    {
        return $this->nextStepName;
    }
}