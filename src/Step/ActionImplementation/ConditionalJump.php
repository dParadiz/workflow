<?php declare(strict_types=1);

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;
use Workflow\Step\Decision;

final class ConditionalJump implements Action
{
    /**
     * @param Decision[] $decisions
     */
    public function __construct(
        private array $decisions = []
    )
    {
    }

    public function execute(Context $context): string
    {
        foreach ($this->decisions as $decision) {
            if ($decision->condition->isSatisfiedBy($context)) {
                return $decision->step;
            }
        }

        return '';
    }
}