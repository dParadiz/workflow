<?php


namespace Workflow;


use Psr\Http\Message\ServerRequestInterface;
use Workflow\Config\Param;

class RequestMapper
{
    /**
     * @var Param[]
     */
    private array $config;

    public function __construct(array $config,)
    {
        $this->config = $config;
    }


    public function toContext(ServerRequestInterface $request): Context
    {
        $context = new Context();

        foreach ($this->config as $key => $value) {
            $extractedValue = match ($value->location) {
                'query' => $request->getQueryParams()[$value->name] ?? null,
                'path' => $this->extractValueFromPath($request->getUri()->getPath(), $value->path, $value->name),
                'body' => $this->extractValueFromBody((array)$request->getParsedBody(), $value->name),
                default => fn () => throw new \RuntimeException('Invalid param location ' . $value->location)
            };

            $context->{$value->name} = $this->map($extractedValue, $value);

        }

        return $context;
    }


    private function extractValueFromPath(string $requestPath, string $path, string $paramName): int|string
    {
        //todo
        return '';
    }

    private function extractValueFromBody(array $parsedBody, string $name): mixed
    {

        $nameParts = explode('.', $name);

        do {
            $key = array_shift($nameParts);
            $value = $parsedBody[$key] ?? null;

            if (!is_array($value)) {
                break;
            }
        } while (count($nameParts));

        return $value;

    }


    private function map(mixed $extractedValue, Param $value): mixed
    {
        return match ($value->type) {
            'int', 'integer' => (int)$extractedValue,
            'bool', 'boolean' => (bool)$extractedValue,
            'string' => (string)$extractedValue,
            'array' => (array)$extractedValue,
            default => class_exists($value->type) ? new ($value->type)($extractedValue) : null
        };
    }

}