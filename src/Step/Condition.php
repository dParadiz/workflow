<?php declare(strict_types=1);

namespace Workflow\Step;

use Workflow\Context;
use Workflow\Evaluator;

final class Condition
{
    public function __construct(private string $statement)
    {
    }

    public function isSatisfiedBy(Context $context): bool
    {
        $statementEvaluator = new Evaluator(['===', '==', '!=', '!==', '>', '>=', '<', '<=', '(', ')', 'and', 'or', '&&', '||']);

        return (bool)$statementEvaluator->evaluate($this->statement, $context);
    }

}