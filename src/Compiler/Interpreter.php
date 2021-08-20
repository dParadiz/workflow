<?php

namespace Workflow\Compiler;

interface Interpreter
{
    public function stepDefinition(): array;
}