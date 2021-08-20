<?php declare(strict_types=1);

namespace Workflow;

final class Workflow
{
    /** @var Step[] */
    private array $steps = [];

    private string $currentStep;

    public function __construct()
    {
        $this->steps[Step::END_STEP_NAME] = null;
        $this->currentStep = Step::END_STEP_NAME;
    }

    public function addStep(Step $step): Workflow
    {
        unset($this->steps[Step::END_STEP_NAME]);

        $this->steps[$step->getName()] = $step;

        $this->steps[Step::END_STEP_NAME] = null;

        $this->currentStep = array_key_first($this->steps);
        return $this;
    }

    public function execute(Context $context): mixed
    {
        while ($this->currentStep !== Step::END_STEP_NAME) {

            $nextStep = $this->steps[$this->currentStep]->execute($context);

            $this->setCurrentStep($nextStep);
        }

        return $context->return;
    }

    public function reset(): void
    {
        $this->currentStep = array_key_first($this->steps);
    }

    private function setCurrentStep(string $stepName): void
    {
        if ($stepName === '') {
            $stepNames = array_keys($this->steps);
            $this->currentStep = $stepNames[array_search($this->currentStep, $stepNames) + 1];
            return;
        }

        if (isset($this->steps[$stepName])) {
            $this->currentStep = $stepName;
            return;
        }

        $this->currentStep = Step::END_STEP_NAME;
    }
}