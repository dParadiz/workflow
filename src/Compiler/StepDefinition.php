<?php

namespace Workflow\Compiler;

final class StepDefinition
{
    public string $name = '';
    public ?string $next = null;
    public ?string $return = null;

    /** @var array<string, string> */
    public array $assign = [];
}