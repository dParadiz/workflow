<?php declare(strict_types=1);

namespace Workflow\Step;

use Workflow\Context;

interface Action
{
    public function execute(Context $context): mixed;
}