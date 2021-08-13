<?php

namespace Workflow\Step;

use Workflow\Context;

class Condition
{
    public function __construct(private string $variable, private string $operator, private mixed $value)
    {
    }

    public function isSatisfiedBy(Context $context): bool
    {
        $varValue = $context->getVariableValue($this->variable);

        return match ($this->operator) {
            '==' => $varValue === $this->value,
            '!=' => $varValue !== $this->value,
            '>' => $varValue > $this->value,
            '>=' => $varValue >= $this->value,
            '<' => $varValue < $this->value,
            '<=' => $varValue <= $this->value,
        };
    }

}