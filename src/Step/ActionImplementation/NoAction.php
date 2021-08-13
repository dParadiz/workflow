<?php

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;

class NoAction implements Action
{

    public function execute(Context $context): mixed
    {
        return null;
    }
}