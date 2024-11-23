<?php declare(strict_types=1);

namespace Workflow\Step\Action;

use Workflow\Context;

final readonly class Call implements Action
{
    /**
     * @param object $executor
     * @param array<string, mixed> $args
     * @param string $method
     */
    public function __construct(
        private object $executor,
        private array  $args = [],
        private string $method = '__invoke',
    ) {
    }

    public function execute(Context $context): mixed
    {
        $args = array_map(fn (mixed $value): mixed => is_callable($value) ? ($value)($context) : $value, $this->args);

        return $this->executor->{$this->method}(...$args);
    }
}
