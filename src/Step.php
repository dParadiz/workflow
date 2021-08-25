<?php declare(strict_types=1);

namespace Workflow;

use JetBrains\PhpStorm\Pure;
use Workflow\Step\Action;
use Workflow\Step\ExitAction;

final class  Step
{
    public const END_STEP_NAME = 'end';
    private string $name;
    private Action\Action $action;
    private ExitAction\ExitAction $exitAction;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->exitAction = new ExitAction\Next();
        $this->action = new Action\NoAction();

    }

    public function withAction(Action\Action $action): self
    {
        $this->action = $action;
        return $this;
    }


    public function withExitAction(ExitAction\ExitAction $exitAction): self
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
        if ($this->action instanceof Step\Action\ConditionalJump) {
            $nextStep = $this->action->execute($context);

            if ($nextStep === ''
                && $this->exitAction instanceof ExitAction\Next
                && $this->exitAction->execute($context) !== '') {
                $nextStep = $this->exitAction->execute($context);
            }

            return $nextStep;
        }

        $context->actionResult = $this->action->execute($context);

        return $this->exitAction->execute($context);
    }
}