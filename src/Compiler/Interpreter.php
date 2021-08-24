<?php  declare(strict_types=1);

namespace Workflow\Compiler;

interface Interpreter
{
    /**
     * @param array<string,string|array<string, string>> $config
     * @return StepDefinition[]
     */
    public function buildStepDefinition(array $config): array;
}