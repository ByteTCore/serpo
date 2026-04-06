<?php

namespace ByteTCore\Serpo\Commands\Concerns;

trait ResolvesNamespace
{
    protected function resolveConfigNamespace(string $rootNamespace, string $configKey, string $fallback): string
    {
        $namespace = config("serpo.{$configKey}.namespace");

        return $namespace
            ? rtrim($rootNamespace, '\\').'\\'.ltrim($namespace, '\\')
            : $rootNamespace.'\\'.$fallback;
    }
}
