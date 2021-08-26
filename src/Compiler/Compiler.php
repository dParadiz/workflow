<?php declare(strict_types=1);

namespace Workflow\Compiler;


use Symfony\Component\Yaml\Yaml;

final class Compiler
{
    private const LINE_BRAKE = "\n";


    public function __construct(private string $compileDir, private string $configDir)
    {
    }

    public function compile(Interpreter $interpreter, bool $rebuild = false): void
    {
        $code = [];
        $code[] = '<?php  declare(strict_types=1);';
        $code[] = '';
        $code[] = 'return [';

        $this->createCompilationDirectory($this->compileDir);

        $directory = new \RecursiveDirectoryIterator($this->configDir);
        $iterator = new \RecursiveIteratorIterator($directory);
        /** @var \SplFileInfo $info */
        foreach ($iterator as $info) {
            if (!in_array($info->getExtension(), ['yaml', 'yml'])) {
                continue;
            }

            $configName = $info->getBasename('.' . $info->getExtension());


            $code[] = sprintf('    \'%s\' => \DI\factory(function (\Psr\Container\ContainerInterface $di) {', $configName);
            $code[] = '        $workflow = new \Workflow\Workflow();';
            $config = Yaml::parseFile($info->getPathname());
            $steps = $interpreter->buildStepDefinition($config['steps'] ?? []);
            foreach ($steps as $step) {
                $code[] = '        $workflow->addStep(';
                $code[] = '            (new \Workflow\Step(\'' . $step->name . '\'))';

                if ($step->next !== '') {
                    $code[] = '                ->withExitAction(new \Workflow\Step\ExitAction\Next(\'' . $step->next . '\'))';
                }

                if ($step->return !== '') {
                    $code[] = '                ->withExitAction(new \Workflow\Step\ExitAction\ReturnValue(\'' . $step->return . '\'))';
                }

                if ($step->result !== '') {
                    $code[] = '                ->withExitAction(new \Workflow\Step\ExitAction\AssignResult(\'' . $step->result . '\'))';
                }

                if ($step->assign !== []) {
                    $code[] = '                ->withAction(new \Workflow\Step\Action\VariableAssigment([';
                    foreach ($step->assign as $key => $value) {
                        $code[] = "                    '$key' => $value,";
                    }
                    $code[] = '                ]))';
                }


                if ($step->switch !== []) {
                    $code[] = '                ->withAction(new \Workflow\Step\Action\ConditionalJump(';
                    $code[] = '                    [';

                    foreach ($step->switch as $value) {
                        $code[] = '                        new \Workflow\Step\Decision(';
                        $code[] = '                            ' . $value->condition . ',';
                        $code[] = '                            \'' . $value->nextStep . '\'';
                        $code[] = '                        ),';
                    }

                    $code[] = '                    ],';
                    if ($step->next !== null) {
                        $code[] = '                    \'' . $step->next . '\',';
                    }
                    $code[] = '                ))';
                }

                if ($step->call !== null) {
                    $code[] = '                ->withAction(new \Workflow\Step\Action\Call(';
                    $code[] = '                    $di,';
                    $code[] = '                    \'' . $step->call->className . '\',';
                    $code[] = '                    [';
                    foreach ($step->call->arguments as $key => $value) {
                        $code[] = "                        '$key' => $value,";
                    }
                    $code[] = '                    ],';
                    if ($step->call->method !== '') {
                        $code[] = '                    \'' . $step->call->method . '\'';
                    }
                    $code[] = '                 ))';
                }

                $code[] = '        );';
            }

            $code[] = '        return $workflow;';
            $code[] = '    }), ';

        }

        $code[] = '];';

        file_put_contents($this->getDefinitionFilename(), implode(self::LINE_BRAKE, $code));
    }

    public function getDefinitionFilename(): string
    {
        return sprintf('%s/workflow.definitions.php', $this->compileDir);
    }

    private function createCompilationDirectory(string $directory): void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \InvalidArgumentException(sprintf('Compilation directory does not exist and cannot be created: %s . ', $directory));
        }
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException(sprintf('Compilation directory is not writable: %s . ', $directory));
        }
    }

}