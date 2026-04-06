<?php

namespace ByteTCore\Serpo\Commands;

use ByteTCore\Serpo\Commands\Concerns\ResolvesNamespace;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeCriteriaCommand extends GeneratorCommand
{
    use ResolvesNamespace;

    protected $name = 'make:criteria';

    protected $description = 'Create a new criteria class';

    protected $type = 'Criteria';

    protected function getStub(): string
    {
        return __DIR__ . '/../Stubs/criteria.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $this->resolveConfigNamespace($rootNamespace, 'criteria', 'Criteria');
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }
}
