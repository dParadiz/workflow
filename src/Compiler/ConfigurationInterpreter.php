<?php declare(strict_types=1);

namespace Workflow\Compiler;

use PhpParser;

final class ConfigurationInterpreter implements Interpreter
{


    public function buildStepDefinition(array $config): array
    {
        $parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::ONLY_PHP7);
        $contextVariables = new PhpParser\NodeTraverser();
        $contextVariables->addVisitor(new class extends PhpParser\NodeVisitorAbstract {
            public function leaveNode(PhpParser\Node $node)
            {
                if ($node instanceof \PhpParser\Node\Expr\Variable) {
                    return new \PhpParser\Node\Expr\MethodCall(
                        new \PhpParser\Node\Expr\Variable('context'),
                        'getVariableValue',
                        [
                            new \PhpParser\Node\Arg(new PhpParser\Node\Scalar\String_($node->name))
                        ]
                    );
                }
                return $node;

            }
        });

        $steps = [];
        foreach ($config['steps'] as $step) {
            $stepConfig = reset($step);
            $stepName = key($step); // must be string
            $stepViewModel = [
                'name' => $stepName,
            ];


            if (isset($stepConfig['next'])) {
                $stepViewModel['next'] = $stepConfig['next'];
            }

            if (isset($stepConfig['return'])) {
                $stepViewModel['return'] = $stepConfig['return'];
            }

            if (isset($stepConfig['assign'])) {
                $assignments = array_map(function (string $key, mixed $assigment) use ($parser, $contextVariables) {
                    $isDynamicAssigment = is_string($assigment) && preg_match('/^\${(.*)}$/', $assigment, $matches);
                    if ($isDynamicAssigment && isset($matches[1])) {
                        $code = $matches[1];
                        try {
                            $ast = $parser->parse('<?php ' . $code . ';');
                            $astContainsOnlyOneExpression = count($ast) === 1 && $ast[0] instanceof PhpParser\Node\Stmt\Expression;
                            if (!$astContainsOnlyOneExpression) {
                                throw new PhpParser\Error('Only one expression is allowed');
                            }

                            //var_dump($ast);
                            $contextVariables->traverse($ast);

                            //var_dump($ast);
                            $prettyPrinter = new PhpParser\PrettyPrinter\Standard;
                            $code = str_replace('<?php', '', $prettyPrinter->prettyPrintFile($ast));
                            $code = preg_replace('/^(?:[\t ]*(?:\r?\n|\r))+/', '', $code);
                            $code = rtrim($code, ';');


                            return [
                                'variableName' => $key,
                                'variableValue' => 'fn (\Workflow\Context $context) => ' . $code
                            ];

                        } catch (PhpParser\Error $error) {
                            echo "Parse error: {$error->getMessage()}\n";
                            throw $error;
                        }
                    } else {
                        if (is_string($assigment)) {
                            $assigment = "'$assigment'";
                        }

                        return [
                            'variableName' => $key,
                            'variableValue' => $assigment
                        ];

                    }
                }, array_keys($stepConfig['assign']), $stepConfig['assign']);

                $stepViewModel['assignments'] = $assignments;
            }

            $steps[] = $stepViewModel;
        }

        return $steps;
    }
}