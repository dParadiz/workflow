<?php

namespace Workflow\Step\Action;

use Psr\Container\ContainerInterface;
use Workflow\Context;

final class Call implements Action
{
    /**
     * @param ContainerInterface $container
     * @param string $className
     * @param array<string, mixed> $argMap
     * @param string $method
     */
    public function __construct(
        private ContainerInterface $container,
        private string             $className,
        private array              $argMap = [],
        private string             $method = '__invoke',
    )
    {
    }

    public function execute(Context $context): mixed
    {
        if (!$this->container->has($this->className)) {
            throw new \RuntimeException('No definition found in the container for ' . $this->className);
        }
        $process = $this->container->get($this->className);

        $invocableClassRrf = new \ReflectionClass($process);

        try {
            $invokableMethod = $invocableClassRrf->getMethod($this->method);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Process has no function ' . $this->method);
        }

        $arguments = [];
        foreach ($invokableMethod->getParameters() as $parameter) {
            if (!isset($this->argMap[$parameter->getName()]) && !$parameter->isOptional()) {
                throw new \RuntimeException('Required parameter is missing');
            } else if (!isset($this->argMap[$parameter->getName()]) && $parameter->isOptional()) {
                $argValue = $parameter->getDefaultValue();
            } else if (is_callable($this->argMap[$parameter->getName()])) {
                $argValue = ($this->argMap[$parameter->getName()])($context);
            } else {
                $argValue = $this->argMap[$parameter->getName()];
            }

            $arguments[] = $argValue;
        }

        return $invokableMethod->invoke($process, ...$arguments);

    }
}