<?php


namespace Workflow;

final class Workflow
{
    private array $steps = [];
    private array $executionSteps = [];
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

            $this->executionSteps[] = $this->currentStep;

            $nextStep = $this->steps[$this->currentStep]->execute($context);

            $this->setCurrentStep($nextStep);
        }

        return $context->return;
    }

    public function getExecutionSteps(): array
    {
        return $this->executionSteps;
    }

    private function setCurrentStep(string $stepName)
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