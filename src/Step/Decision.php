<?php declare(strict_types=1);

namespace Workflow\Step;

final class Decision
{

    public function __construct(public Condition $condition, public string $step)
    {
    }
}