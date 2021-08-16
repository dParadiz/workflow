<?php declare(strict_types=1);

namespace Workflow\Step\ActionImplementation;

use Workflow\Context;
use Workflow\Step\Action;

final class VariableAssigment implements Action
{
    public function __construct(private array $assignments = [])
    {
    }

    public function execute(Context $context): mixed
    {
        foreach ($this->assignments as $key => $value) {
            $value = $this->evaluateValue($value, $context);

            $context->assign($key, $value);
        }

        return null;
    }

    private function getVariableValue(string $variable, Context $context): mixed
    {
        $value = $context->getVariableValue($variable);

        if (is_string($value)) {
            return '"' . $value . '"';
        }

        return $value;
    }


    public function evaluateValue(mixed $value, Context $context): mixed
    {
        $matches = [];
        $isParsableString = is_string($value) && preg_match('/^\${(.*)}$/', $value, $matches);

        if ($isParsableString) {

            $evalString = $matches[1];
            $evalStringParts = explode(' ', $evalString);
            $parsedParts = [];

            foreach ($evalStringParts as $part) {
                if (is_numeric($part)
                    || preg_match('/^".*"$/', $part)
                    || in_array($part, ['+', '-', '*', '/', '.']) // operators
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
            $value = $output;

        }

        return $value;
    }
}