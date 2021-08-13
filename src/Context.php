<?php


namespace Workflow;


class Context
{
    private array $data = [];

    public mixed $return = null;

    public mixed $actionResult = null;

    public function assign(string $varName, mixed $value)
    {
        $this->data[$varName] = $value;
    }


    public function getVariableValue(string $variable): mixed
    {
        return $this->data[$variable];
    }


}