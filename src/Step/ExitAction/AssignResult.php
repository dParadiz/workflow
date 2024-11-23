<?php declare(strict_types=1);

namespace Workflow\Step\ExitAction;

use Workflow\Context;

final readonly class AssignResult implements ExitAction
{
    public function __construct(private string $variable)
    {
    }

    public function execute(Context $context): string
    {
        $context[$this->variable] = $context->actionResult;

        return '';
    }
}
