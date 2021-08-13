<?php declare(strict_types=1);

namespace Workflow;


final class Context
{
    private array $data = [];
    public mixed $return = null;
    public mixed $actionResult = null;

    public function assign(string $varName, mixed $value): void
    {
        $this->data[$varName] = $value;
    }

    public function getVariableValue(string $variable): mixed
    {
        return $this->data[$variable];
    }
}