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

        $workflow->addStep(new \Workflow\Step('step1'));
        $workflow->addStep(new \Workflow\Step('step2'));
        $workflow->addStep(new \Workflow\Step('step3'));

        $workflow->execute($context);

        static::assertEquals(['step1', 'step2', 'step3'], $workflow->getExecutionSteps());
    }


    public function test_flow_control_execution()
    {
        $context = new \Workflow\Context();
        $workflow = new \Workflow\Workflow();

        $step1 = new \Workflow\Step('step1');
        $step1->withExitAction(new Next('step3'));

        $step3 = new \Workflow\Step('step3');
        $step3->withExitAction(new Next('step2'));

        $step2 = new \Workflow\Step('step2');
        $step2->withExitAction(new Next('end'));


        $workflow->addStep($step1);
        $workflow->addStep($step2);
        $workflow->addStep($step3);

        $workflow->execute($context);

        static::assertEquals(['step1', 'step3', 'step2'], $workflow->getExecutionSteps());
    }

    public function test_return_value()
    {
        $context = new \Workflow\Context();
        $context->assign('a', 2);
        $workflow = new \Workflow\Workflow();

        $workflow->addStep(new \Workflow\Step('step1'));
        $workflow->addStep((new \Workflow\Step('step2'))->withExitAction(new ReturnValue('a')));
        $workflow->addStep(new \Workflow\Step('step3'));

        $value = $workflow->execute($context);

        static::assertEquals(['step1', 'step2'], $workflow->getExecutionSteps());
        static::assertEquals(2, $value);
    }

    public function test_conditional_switch()
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context->assign('a', 1);

        $workflow->addStep((new \Workflow\Step('step1')));
        $workflow->addStep((new \Workflow\Step('step2'))->withAction(new \Workflow\Step\ActionImplementation\ConditionalJump([
            new \Workflow\Step\Decision(new \Workflow\Step\Condition('${a !==  1}'), \Workflow\Step::END_STEP_NAME),
            new \Workflow\Step\Decision(new \Workflow\Step\Condition('${a ===  1}'), 'step4'),
        ])));
        $workflow->addStep(new \Workflow\Step('step3'));
        $workflow->addStep(new \Workflow\Step('step4'));


        $workflow->execute($context);

        static::assertEquals(['step1', 'step2', 'step4'], $workflow->getExecutionSteps());

    }

    public function test_variable_assigment()
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context->assign('a', 1);

        $workflow->addStep((new \Workflow\Step('step1')));
        $workflow->addStep((new \Workflow\Step('step2'))->withAction(new \Workflow\Step\ActionImplementation\VariableAssigment([
            'test' => 123,
            'test2' => 3,
            'test3' => '${ test  - test2}',
            'test4' => '${ (string)test  . (string)test2}'
        ])));
        $workflow->addStep(new \Workflow\Step('step3'));


        $workflow->execute($context);

        static::assertEquals(['step1', 'step2', 'step3'], $workflow->getExecutionSteps());

        static::assertEquals(123, $context->getVariableValue('test'));
        static::assertEquals(120, $context->getVariableValue('test3'));
        static::assertEquals(1233, $context->getVariableValue('test4'));
    }
}