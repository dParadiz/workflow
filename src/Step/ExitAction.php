<?php declare(strict_types=1);

namespace Workflow\Step;

use Workflow\Context;

interface ExitAction
{
    public function execute(Context $context): string;
}