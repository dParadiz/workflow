<?php declare(strict_types=1);

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;

final class NoAction implements Action
{

    public function execute(Context $context): mixed
    {
        return null;
    }
}