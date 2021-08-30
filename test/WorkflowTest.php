<?php

use PHPUnit\Framework\TestCase;
use Workflow\Step\ExitAction\Next;
use Workflow\Step\ExitAction\ReturnValue;

class WorkflowTest extends TestCase
{
    // pipeline example
    public function test_sequential_execution(): void
    {
        $context = new \Workflow\Context();
        $workflow = new \Workflow\Workflow();

        $workflow->addStep(
            (new \Workflow\Step('step1'))
                ->withAction(new \Workflow\Step\Action\VariableAssigment(['a' => 'step1']))
        );

        $workflow->addStep((new \Workflow\Step('step2'))
            ->withAction(new \Workflow\Step\Action\VariableAssigment([
                'a' => fn (\Workflow\Context $context) => $context['a'] . ' step2'
            ]))
        );
        $workflow->addStep((new \Workflow\Step('step3'))
            ->withAction(new \Workflow\Step\Action\VariableAssigment([
                'a' => fn (\Workflow\Context $context) => $context['a'] . ' step3'
            ]))
            ->withExitAction(new ReturnValue('a'))
        );

        $result = $workflow->execute($context);

        static::assertEquals('step1 step2 step3', $result);
    }


    public function test_controlled_flow_execution(): void
    {
        $context = new \Workflow\Context();
        $workflow = new \Workflow\Workflow();

        $step1 = new \Workflow\Step('step1');
        $step1->withAction(new \Workflow\Step\Action\VariableAssigment(['a' => 'step1']))
            ->withExitAction(new Next('step3'));

        $step3 = new \Workflow\Step('step3');
        $step3->withAction(
            new \Workflow\Step\Action\VariableAssigment([
                    'a' => fn (\Workflow\Context $context) => $context['a'] . ' step3']
            ))->withExitAction(new Next('step2'));

        $step2 = new \Workflow\Step('step2');
        $step2->withAction(
            new \Workflow\Step\Action\VariableAssigment(
                ['a' => fn (\Workflow\Context $context) => $context['a'] . ' step2']
            ))->withExitAction(new ReturnValue('a'));


        $workflow->addStep($step1);
        $workflow->addStep($step2);
        $workflow->addStep($step3);

        $result = $workflow->execute($context);

        static::assertEquals('step1 step3 step2', $result);
    }

    public function test_return_value(): void
    {
        $context = new \Workflow\Context();
        $context['a'] = 2;
        $workflow = new \Workflow\Workflow();

        $workflow->addStep((new \Workflow\Step('step1'))->withExitAction(new ReturnValue('a')));

        $value = $workflow->execute($context);

        static::assertEquals(2, $value);
    }

    public function test_conditional_switch(): void
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context['decision'] = 'step4';
        $context['success'] = true;
        $context['failed'] = false;

        $workflow->addStep((new \Workflow\Step('step1')));
        $workflow->addStep((new \Workflow\Step('step2'))->withAction(new \Workflow\Step\Action\ConditionalJump([
            new \Workflow\Step\Decision(fn (\Workflow\Context $context): bool => $context['decision'] === 'step4', 'step4'),
        ])));
        $workflow->addStep((new \Workflow\Step('step3'))->withExitAction(new ReturnValue('failed')));
        $workflow->addStep((new \Workflow\Step('step4'))->withExitAction(new ReturnValue('success')));


        $result = $workflow->execute($context);

        static::assertTrue($result);

    }

    public function test_variable_assigment(): void
    {
        $workflow = new \Workflow\Workflow();

        $context = new \Workflow\Context();
        $context['a'] = 1;

        $workflow->addStep((new \Workflow\Step('step1'))->withAction(new \Workflow\Step\Action\VariableAssigment([
            'test' => 123,
            'test2' => 3,
            'test3' => fn (\Workflow\Context $context): int => $context['test'] - $context['test2'],
            'test4' => fn (\Workflow\Context $context): string => $context['test'] . $context['test2'],
        ])));

        $workflow->execute($context);


        static::assertEquals(123, $context['test']);
        static::assertEquals(120, $context['test3']);
        static::assertEquals(1233, $context['test4']);
    }
}
