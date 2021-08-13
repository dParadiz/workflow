<?php


namespace Workflow;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Workflow\Config\Param;

class WorkflowRequestHandler implements RequestHandlerInterface
{
    private array $tasks = [];

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $param1 = new Param();
        $param1->name = 'id';
        $param1->location = 'path';
        $param1->path = '/res/{id}';
        $param1->type = 'integer';

        $requestMapper = new RequestMapper([
            $param1
        ]);
        $context = $requestMapper->toContext($request);

        $result = null;

        $steps = [
            'callable' => [

            ]
        ];
        // task 1 execute -- can have only request as input
        // task 2 execute -- can have context from previous task and request
        // task 3 task 4 -- based on request and previous task outcome
    }

}