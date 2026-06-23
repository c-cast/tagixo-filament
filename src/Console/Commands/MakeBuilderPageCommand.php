<?php

namespace Ccast\TagixoFilament\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:builder-page')]
class MakeBuilderPageCommand extends GeneratorCommand
{
    /**
     * The console command name.
     */
    protected $name = 'make:builder-page';

    /**
     * The console command description.
     */
    protected $description = 'Create a new Visual Builder page';

    /**
     * The type of class being generated.
     */
    protected $type = 'Builder Page';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/builder-page.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        $panel = $this->option('panel') ?? 'Admin';

        return $rootNamespace.'\\Filament\\'.$panel.'\\Pages';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $context = $this->option('context') ?? 'page';
        $title = $this->option('title') ?? Str::headline(class_basename($name));

        return str_replace(
            ['{{ context }}', '{{ title }}'],
            [$context, $title],
            $stub
        );
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the page'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['context', 'c', InputOption::VALUE_OPTIONAL, 'The builder context (page, form, mail, pdf)', 'page'],
            ['title', 't', InputOption::VALUE_OPTIONAL, 'The page title'],
            ['panel', 'p', InputOption::VALUE_OPTIONAL, 'The Filament panel name', 'Admin'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the page already exists'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => [
                'What should the builder page be named?',
                'E.g. MyPageBuilder',
            ],
        ];
    }
}
