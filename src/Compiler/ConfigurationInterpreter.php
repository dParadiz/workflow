<?php declare(strict_types=1);

namespace Workflow\Compiler;

use http\Exception\RuntimeException;
use PhpParser;

final class ConfigurationInterpreter implements Interpreter
{

    private ?PhpParser\Parser $parser = null;
    private ?PhpParser\NodeTraverser $nodeTraverser = null;

    /**
     * @param array<string,array> $config
     * @return Definition\Step[]
     */
    public function buildStepDefinition(array $config): array
    {
        $steps = [];
        foreach ($config as $step) {
            $stepConfig = reset($step);
            $stepName = (string)key($step); // must be string
            $stepDefinition = new Definition\Step();
            $stepDefinition->name = $stepName;

            $stepDefinition->next = $stepConfig['next'] ?? null;
            $stepDefinition->return = $stepConfig['return'] ?? null;

            foreach ($stepConfig['assign'] ?? [] as $key => $assigment) {
                $isDynamicAssigment = is_string($assigment) && preg_match('/^\${(.*)}$/', $assigment, $matches);
                if ($isDynamicAssigment && isset($matches[1])) {
                    $assigment = $this->parse((string)$matches[1]);
                } else if (is_string($assigment)) {
                    $assigment = "'$assigment'";
                }

                $stepDefinition->assign[$key] = $assigment;
            }

            foreach ($stepConfig['switch'] ?? [] as $switchDecision) {
                $isDynamicAssigment = is_string($switchDecision['condition']) && preg_match('/^\${(.*)}$/', $switchDecision['condition'], $matches);
                if (!$isDynamicAssigment || !isset($matches[1])) {
                    throw new RuntimeException('Only expressions are allowed for conditions');
                }
                $condition = $this->parse((string)$matches[1]);

                $stepDefinition->switch[] = new Definition\Decision($condition, (string)$switchDecision['step']);

            }

            $steps[] = $stepDefinition;
        }

        return $steps;
    }


    public function parse(string $code): string
    {
        $ast = (array)$this->parser()->parse('<?php ' . $code . ';');

        $astContainsOnlyOneExpression = count($ast) === 1 && $ast[0] instanceof PhpParser\Node\Stmt\Expression;
        if (!$astContainsOnlyOneExpression) {
            throw new PhpParser\Error('Only one expression is allowed');
        }

        $this->nodeTraverser()->traverse($ast);

        $prettyPrinter = new PhpParser\PrettyPrinter\Standard;
        $code = (string)str_replace('<?php', '', $prettyPrinter->prettyPrintFile($ast));
        $code = (string)preg_replace('/^(?:[\t ]*(?:\r?\n|\r))+/', '', $code);
        $code = rtrim($code, ';');


        return 'fn (\Workflow\Context $context) => ' . $code;
    }

    private function parser(): PhpParser\Parser
    {
        if ($this->parser === null) {
            $this->parser = (new PhpParser\ParserFactory)->create(PhpParser\ParserFactory::ONLY_PHP7);
        }

        return $this->parser;
    }

    private function nodeTraverser(): PhpParser\NodeTraverser
    {
        if ($this->nodeTraverser === null) {
            $this->nodeTraverser = new PhpParser\NodeTraverser();
            $this->nodeTraverser->addVisitor(new class extends PhpParser\NodeVisitorAbstract {
                public function leaveNode(PhpParser\Node $node)
                {
                    if ($node instanceof \PhpParser\Node\Expr\Variable) {
                        return new \PhpParser\Node\Expr\MethodCall(
                            new \PhpParser\Node\Expr\Variable('context'),
                            'valueOf',
                            [
                                new \PhpParser\Node\Arg(new PhpParser\Node\Scalar\String_($node->name))
                            ]
                        );
                    }
                    return $node;

                }
            });
        }
        return $this->nodeTraverser;
    }
}