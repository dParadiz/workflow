<?php declare(strict_types=1);

namespace Workflow\Step\ExitAction;

use Workflow\Context;
use Workflow\Step;

final readonly class ReturnValue implements ExitAction
{
    public function __construct(private string $variable)
    {

    }

    public function execute(Context $context): string
    {
        $context->return = $context[$this->variable];
        return Step::END_STEP_NAME;
    }

}
