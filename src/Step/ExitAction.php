<?php

namespace Workflow\Step;

use Workflow\Context;

interface ExitAction
{
    public function execute(Context $context): string;
}