<?php declare(strict_types=1);

namespace Workflow\Step;

use Closure;

final class Decision
{

    public function __construct(public Closure $condition, public string $step)
    {
    }
}