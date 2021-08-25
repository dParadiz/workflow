<?php

namespace Workflow\Compiler\Definition;

class Decision
{

    public function __construct(public string $condition, public string $nextStep)
    {

    }


}