<?php declare(strict_types=1);

namespace Workflow\Step\Action;

use Workflow\Context;

final class NoAction implements Action
{

    public function execute(Context $context): mixed
    {
        return null;
    }
}