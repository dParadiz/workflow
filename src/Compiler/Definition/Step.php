<?php

namespace Workflow\Compiler\Definition;

final class Step
{
    public string $name = '';
    public ?string $next = null;
    public ?string $return = null;

    /** @var array<string, string> */
    public array $assign = [];

    /** @var Decision[] */
    public array $switch = [];
}