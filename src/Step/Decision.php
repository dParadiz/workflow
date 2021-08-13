<?php

namespace Workflow\Step;

class Decision
{

    public function __construct(public Condition $condition, public string $step)
    {
    }
}