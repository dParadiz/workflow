<?php

use PHPUnit\Framework\TestCase;
use Workflow\Context;

class CompilerTest extends TestCase
{
    private \Psr\Container\ContainerInterface $di;

    public function setUp(): void
    {
        parent::setUp();

        $compilePath = realpath(__DIR__ . '/../var/workflow');
        $compiler = new Workflow\Compiler\Compiler(
            (string)$compilePath,
            __DIR__ . '/config',
        );

        $compiler->compile(new \Workflow\Compiler\ConfigurationInterpreter());
        $definitions = include $compiler->getDefinitionFilename();
        $builder = new \DI\ContainerBuilder();

        $builder->addDefinitions($definitions);
        $this->di = $builder->build();

    }

    public function test_build_from_yaml(): void
    {
        /** @var \Workflow\Workflow $workflow */
        $workflow = $this->di->get('workflow-example');
        $context = new Context();
        $context['a'] = 1;
        $result = $workflow->execute($context);

        self::assertEquals('String with value = 2', $result);


        $context = new Context();
        $context['a'] = 3;
        $result = $workflow->execute($context);

        self::assertEquals('Value is 3', $result);

    }

    public function test_with_call_action(): void
    {
        if (method_exists($this->di, 'set')) {
            $this->di->set(
                'Increment',
                new class {
                    public function __invoke(int $value, int $incrementBy): int
                    {
                        return $value + $incrementBy;
                    }
                });
        }

        $workflow = $this->di->get('custom-action-workflow');

        $context = new Context();
        $context['a'] = 1;
        $result = $workflow->execute($context);

        self::assertTrue($result);
    }


}
