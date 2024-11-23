<?php declare(strict_types=1);

namespace Workflow\Step\ExitAction;

use Workflow\Context;

final readonly class Next implements ExitAction
{
    public function __construct(private string $nextStepName = '')
    {
    }

    public function execute(Context $context): string
    {
        return $this->nextStepName;
    }
}