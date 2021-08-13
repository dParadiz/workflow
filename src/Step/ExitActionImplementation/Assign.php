<?php

namespace Workflow\Step\ExitActionImplementation;

use Workflow\Context;
use Workflow\Step\ExitAction;

class Assign implements ExitAction
{
    public function __construct(private string $variable)
    {
    }

    public function execute(Context $context): string
    {
        $context->assign($this->variable, $context->actionResult);

        return '';
    }
}