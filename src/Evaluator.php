<?php

namespace Workflow;

class Evaluator
{
    private array $allowedOperations = [];


    public function __construct(array $allowedOperations)
    {
        $this->allowedOperations = $allowedOperations;
    }


    public function evaluate(mixed $value, Context $context): mixed
    {
        $matches = [];
        $isValidEvaluationString = is_string($value) && preg_match('/^\${(.*)}$/', $value, $matches);
        if (!$isValidEvaluationString) {
            return $value;
        }

        $stringForEvaluation = $matches[1];
        $evaluationParts = explode(' ', $stringForEvaluation);
        $parsedParts = [];

        foreach ($evaluationParts as $part) {
            if (is_numeric($part)
                || preg_match('/^".*"$/', $part)
                || in_array($part, $this->allowedOperations)
            ) {
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

        $output = null;

        try {
            eval('$output= ' . $parsedString . ';');
        } catch (\ParseError $e) {
            throw new \RuntimeException('Failed to evaluate ' . $parsedString . ' with error:' . $e->getMessage());
        }

        return $output;
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