<?php

namespace Workflow\Step\ExitActionImplementation;

use Workflow\Context;
use Workflow\Step;

class ReturnValue implements Step\ExitAction
{
    public function __construct(private string $variable)
    {

    }

    public function execute(Context $context): string
    {
        $context->return = $context->getVariableValue($this->variable);
        return Step::END_STEP_NAME;
    }

}