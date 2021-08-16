<?php declare(strict_types=1);

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;
use Workflow\Evaluator;

final class VariableAssigment implements Action
{
    public function __construct(private array $assignments = [])
    {
    }

    public function execute(Context $context): mixed
    {
        $valueEvaluator = new Evaluator(['+', '-', '*', '/', '.']);
        foreach ($this->assignments as $key => $value) {

            $context->assign($key, $valueEvaluator->evaluate($value, $context));
        }

        return null;
    }

}