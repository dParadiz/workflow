<?php declare(strict_types=1);

namespace Workflow\Step\Action;

use Workflow\Context;
use Workflow\Step\Decision;

final readonly class ConditionalJump implements Action
{
    /**
     * @param Decision[] $decisions
     */
    public function __construct(
        private array  $decisions = [],
        private string $defaultStep = ''
    )
    {
    }

    public function execute(Context $context): string
    {
        foreach ($this->decisions as $decision) {
            if (($decision->condition)($context)) {
                return $decision->step;
            }
        }

        return $this->defaultStep;
    }
}