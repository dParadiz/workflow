<?php declare(strict_types=1);

namespace Workflow\Step\Action;

use Workflow\Context;

final class VariableAssigment implements Action
{
    /**
     * @param array<string, callable|mixed> $assignments
     */
    public function __construct(private array $assignments = [])
    {
    }

    public function execute(Context $context): mixed
    {
        foreach ($this->assignments as $key => $value) {
            if (is_callable($value)) {
                $value = $value($context);
            }

            $context[$key] = $value;
        }

        return null;
    }

}
