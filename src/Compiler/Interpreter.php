<?php  declare(strict_types=1);

namespace Workflow\Compiler;

interface Interpreter
{
    public function buildStepDefinition(array $config): array;
}