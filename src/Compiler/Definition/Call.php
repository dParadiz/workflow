<?php

namespace Workflow\Compiler\Definition;

final class Call
{
    /**
     * @param string $className
     * @param array<string, mixed> $arguments
     * @param string $method
     */
    public function __construct(public string $className, public array $arguments = [], public string $method = '')
    {

    }


}