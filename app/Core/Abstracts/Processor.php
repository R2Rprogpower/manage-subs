<?php

declare(strict_types=1);

namespace App\Core\Abstracts;

abstract class Processor
{
    // Base processor class
    // All processors should extend this class
    // Processors should override execute() with specific signatures for type safety
    // This allows better type checking, IDE autocomplete, and static analysis
}
