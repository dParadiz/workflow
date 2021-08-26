<?php

namespace Workflow\Compiler\Definition;

final class Step
{
    public string $name = '';
    public string $next = '';
    public string $result = '';
    public string $return = '';

    /** @var array<string, string> */
    public array $assign = [];

    /** @var Decision[] */
    public array $switch = [];

    public ?Call $call = null;
}