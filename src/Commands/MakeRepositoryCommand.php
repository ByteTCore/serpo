<?php

namespace ByteTCore\Serpo\Commands;

use ByteTCore\Serpo\Commands\Concerns\ResolvesNamespace;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeRepositoryCommand extends GeneratorCommand
{
    use ResolvesNamespace;

    protected $name = 'make:repository';

    protected $description = 'Create a new repository class';

    protected $type = 'Repository';

    protected function getStub(): string
    {
        return __DIR__.'/../Stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $this->resolveConfigNamespace($rootNamespace, 'repository', 'Repositories');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);
        $model = $this->option('model') ?? str_replace('Repository', '', class_basename($name));
        $modelNamespace = $this->qualifyModel($model);

        return str_replace(
            ['DummyModelNamespace', 'DummyModel'],
            [$modelNamespace, class_basename($modelNamespace)],
            $stub
        );
    }

    public function handle(): ?bool
    {
        $result = parent::handle();

        if ($this->option('service')) {
            $this->call('make:service', [
                'name' => str_replace('Repository', 'Service', $this->argument('name')),
                '--repository' => $this->argument('name'),
                '--force' => $this->option('force'),
            ]);
        }

        return $result;
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the repository applies to'],
            ['service', 's', InputOption::VALUE_NONE, 'Create corresponding service class'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }
}
