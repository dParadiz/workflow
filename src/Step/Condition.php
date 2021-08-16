<?php declare(strict_types=1);

namespace Workflow\Step;

use Workflow\Context;

final class Condition
{
    public function __construct(private string $statement)
    {
    }

    public function isSatisfiedBy(Context $context): bool
    {
        $statementParts = explode(' ', $this->statement);
        $parsedParts = [];
        foreach ($statementParts as $part) {
            if (
                is_numeric($part)
                || preg_match('/^".*"$/', $part)
                || in_array($part, ['===', '==', '!=', '!==', '>', '>=', '<', '<=', '(', ')', 'and', 'or', '&&', '||'])) {
                $parsedParts[] = $part;
                continue;
            }

            $typeCastMatch = [];
            $isTypeCastingUsed = preg_match('/^(\(string\)|\(int\)|\(bool\)|\(float\))(.*)/', $part, $typeCastMatch);
            if ($isTypeCastingUsed) {

                $parsedParts[] = $typeCastMatch[1];
                $parsedParts[] = $context->isVariableSet($typeCastMatch[2]) ? $this->getVariableValue($typeCastMatch[2], $context) : $typeCastMatch[2];

                continue;
            }

            if ($context->isVariableSet($part)) {
                $parsedParts[] = $this->getVariableValue($part, $context);
            }
        }

        $parsedString = implode(' ', $parsedParts);

        $output = false;
        try {
            eval('$output= ' . $parsedString . ';');
        } catch (\ParseError $e) {
            throw new \RuntimeException('Failed to evaluate ' . $parsedString . ' with error:' . $e->getMessage());
        }
        return (bool)$output;
    }

    private function getVariableValue(string $variable, Context $context): mixed
    {
        $value = $context->getVariableValue($variable);

        if (is_string($value)) {
            return '"' . $value . '"';
        }

        return $value;
    }

}