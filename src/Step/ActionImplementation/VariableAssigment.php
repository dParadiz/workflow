<?php

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;

class VariableAssigment implements Action
{
    public function __construct(private array $assignments = [])
    {
    }

    public function execute(Context $context): mixed
    {
        foreach ($this->assignments as $key => $value) {
            $context->assign($key, $value);
        }

        return null;
    }
}