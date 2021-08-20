<?php

use PHPUnit\Framework\TestCase;
use Workflow\Step\ExitActionImplementation\Next;
use Workflow\Step\ExitActionImplementation\ReturnValue;

class WorkflowTest extends TestCase
{
    // pipeline example
    public function test_sequential_execution()
    {
        $context = new \Workflow\Context();
        $workflow = new \Workflow\Workflow();

        $workflow->addStep(
            (new \Workflow\Step('step1'))
                ->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment(['a' => 'step1']))
        );

        $workflow->addStep((new \Workflow\Step('step2'))
            ->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment([
                'a' => fn (\Workflow\Context $context) => $context->getVariableValue('a') . ' step2'
            ]))
        );
        $workflow->addStep((new \Workflow\Step('step3'))
            ->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment([
                'a' => fn (\Workflow\Context $context) => $context->getVariableValue('a') . ' step3'
            ]))
            ->withExitAction(new ReturnValue('a'))
        );

        $result = $workflow->execute($context);

        static::assertEquals('step1 step2 step3', $result);
    }


    public function test_controlled_flow_execution()
    {
        $context = new \Workflow\Context();
        $workflow = new \Workflow\Workflow();

        $step1 = new \Workflow\Step('step1');
        $step1->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment(['a' => 'step1']))
            ->withExitAction(new Next('step3'));

        $step3 = new \Workflow\Step('step3');
        $step3->withAction(
            new \Workflow\Step\ActionImplementation\VariableAssigment([
                    'a' => fn (\Workflow\Context $context) => $context->getVariableValue('a') . ' step3']
            ))->withExitAction(new Next('step2'));

        $step2 = new \Workflow\Step('step2');
        $step2->withAction(
            new \Workflow\Step\ActionImplementation\VariableAssigment(
                ['a' => fn (\Workflow\Context $context) => $context->getVariableValue('a') . ' step2']
            ))->withExitAction(new ReturnValue('a'));


        $workflow->addStep($step1);
        $workflow->addStep($step2);
        $workflow->addStep($step3);

        $result = $workflow->execute($context);

        static::assertEquals('step1 step3 step2', $result);
    }

    public function test_return_value()
    {
        $context = new \Workflow\Context();
        $context->assign('a', 2);
        $workflow = new \Workflow\Workflow();

        $workflow->addStep((new \Workflow\Step('step1'))->withExitAction(new ReturnValue('a')));

        $value = $workflow->execute($context);

        static::assertEquals(2, $value);
    }

    public function test_conditional_switch()
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context->assign('decision', 'step4');
        $context->assign('success', true);
        $context->assign('failed', false);

        $workflow->addStep((new \Workflow\Step('step1')));
        $workflow->addStep((new \Workflow\Step('step2'))->withAction(new \Workflow\Step\ActionImplementation\ConditionalJump([
            new \Workflow\Step\Decision(fn (\Workflow\Context $context): bool => $context->getVariableValue('decision') === 'step4', 'step4'),
        ])));
        $workflow->addStep((new \Workflow\Step('step3'))->withExitAction(new ReturnValue('failed')));
        $workflow->addStep((new \Workflow\Step('step4'))->withExitAction(new ReturnValue('success')));


        $result = $workflow->execute($context);

        static::assertTrue($result);

    }

    public function test_variable_assigment()
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context->assign('a', 1);

        $workflow->addStep((new \Workflow\Step('step1'))->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment([
            'test' => 123,
            'test2' => 3,
            'test3' => fn (\Workflow\Context $context): int => $context->getVariableValue('test') - $context->getVariableValue('test2'),
            'test4' => fn (\Workflow\Context $context): string => $context->getVariableValue('test') . $context->getVariableValue('test2'),
        ])));

        $workflow->execute($context);


        static::assertEquals(123, $context->getVariableValue('test'));
        static::assertEquals(120, $context->getVariableValue('test3'));
        static::assertEquals(1233, $context->getVariableValue('test4'));
    }
}