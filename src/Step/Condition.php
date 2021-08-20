<?php declare(strict_types=1);

namespace Workflow\Step;

use Workflow\Context;

interface Condition
{
    public function isSatisfiedBy(Context $context): bool;
}