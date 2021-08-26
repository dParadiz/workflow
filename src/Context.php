<?php declare(strict_types=1);

namespace Workflow;


final class Context
{
    /** @var array<string,mixed> */
    private array $data = [];
    public mixed $return = null;
    public mixed $actionResult = null;

    public function assign(string $variable, mixed $value): void
    {
        $this->data[$variable] = $value;
    }

    public function valueOf(string $variable): mixed
    {
        return $this->data[$variable];
    }

}