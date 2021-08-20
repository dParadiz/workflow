<?php

use PHPUnit\Framework\TestCase;
use Workflow\Context;

class Compiler extends TestCase
{

    public function test_build_from_yaml()
    {
        $compiler = new Workflow\Compiler\Compiler(
            __DIR__ . '/tmp/workflow',
            __DIR__ . '/config',
        );

        $compiler->compile(new \Workflow\Compiler\ConfigurationInterpreter());
        $definitions = require_once $compiler->getDefinitionFilename();
        $builder = new \DI\ContainerBuilder();

        $builder->addDefinitions($definitions);
        $builder->enableCompilation(__DIR__ . '/tmp/di');
        $di = $builder->build();
        $workflow = $di->get('implicit_step_ordering');

        $result = $workflow->execute(new Context());


        self::assertEquals('String with value = 2', $result);

    }


}