<?php

namespace ByteTCore\Serpo\Commands;

use ByteTCore\Serpo\Commands\Concerns\ResolvesNamespace;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeServiceCommand extends GeneratorCommand
{
    use ResolvesNamespace;

    protected $name = 'make:service';

    protected $description = 'Create a new service class';

    protected $type = 'Service';

    protected function getStub(): string
    {
        return __DIR__ . '/../Stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $this->resolveConfigNamespace($rootNamespace, 'service', 'Services');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);
        $repoNamespace = $this->qualifyRepository($name);
        $repoClass = class_basename($repoNamespace);

        return str_replace(
            ['DummyRepositoryNamespace', '$DummyRepository', 'DummyRepository'],
            [$repoNamespace, '$' . lcfirst($repoClass), $repoClass],
            $stub
        );
    }

    protected function qualifyRepository(string $name): string
    {
        $rootNamespace = $this->laravel->getNamespace();
        $repository = $this->option('repository')
            ?? str_replace('Service', 'Repository', class_basename($name));

        if (Str::startsWith($repository, $rootNamespace)) {
            return $repository;
        }

        $repository = str_replace('/', '\\', $repository);
        $repoNamespace = $this->resolveConfigNamespace(trim($rootNamespace, '\\'), 'repository', 'Repositories');

        return $repoNamespace . '\\' . $repository;
    }

    protected function getOptions(): array
    {
        return [
            ['repository', 'r', InputOption::VALUE_OPTIONAL, 'The repository that the service depends on'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the service already exists'],
        ];
    }
}
