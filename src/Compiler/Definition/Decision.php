<?php

namespace Workflow\Compiler\Definition;

final class Decision
{

    public function __construct(public string $condition, public string $nextStep)
    {

    }


}