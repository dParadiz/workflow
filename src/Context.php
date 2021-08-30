<?php declare(strict_types=1);

namespace Workflow;

/**
 * @extends \ArrayObject<string, mixed>
 */
final class Context extends \ArrayObject
{
    public mixed $return = null;
    public mixed $actionResult = null;
}
