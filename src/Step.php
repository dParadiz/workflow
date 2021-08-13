<?php


namespace Workflow;

use JetBrains\PhpStorm\Pure;
use Workflow\Step\Action;
use Workflow\Step\ExitAction;
use Workflow\Step\ExitActionImplementation\Assign;

final class  Step
{
    public const END_STEP_NAME = 'end';
    private string $name;
    private Action $action;
    private ExitAction $exitAction;

    #[Pure]
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->exitAction = new Step\ExitActionImplementation\Next();
        $this->action = new Step\ActionImplementation\NoAction();

    }

    public function withAction(Action $action): self
    {
        $this->action = $action;
        return $this;
    }


    public function withExitAction(ExitAction $exitAction): self
    {
        $this->exitAction = $exitAction;
        return $this;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function execute(Context $context): string
    {
        if ($this->action instanceof Step\ActionImplementation\ConditionalJump) {
            return $this->action->execute($context);
        }

        $context->actionResult = $this->action->execute($context);

        return $this->exitAction->execute($context);
    }
}