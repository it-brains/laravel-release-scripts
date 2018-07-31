<?php

namespace ITBrains\ReleaseScript;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ScriptService
{
    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var ScriptRepositoryInterface
     */
    private $scriptsRepository;

    private $notes = [];

    /**
     * ScriptService constructor.
     * @param Filesystem $files
     * @param ScriptRepositoryInterface $scriptsRepository
     */
    public function __construct(Filesystem $files, ScriptRepositoryInterface $scriptsRepository)
    {
        $this->files = $files;
        $this->scriptsRepository = $scriptsRepository;
    }


    public function run($paths = [], $class = null)
    {
        if ($class) {
            if (! class_exists($class)) {
                $this->note("Class '{$class}' does not exist!");

                return;
            }

            app()->make($class)->run();

            return;
        }

        $files = $this->getScriptsFiles($paths);

        $ranScripts = $this->scriptsRepository->getRan();

        $scripts = $this->pendingScripts($files, $ranScripts);

        $this->requireFiles($scripts);

        $this->runPending($scripts);

        return $scripts;
    }

    /**
     * Require in all the script files in a given path.
     *
     * @param  array $files
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Run an array of scripts.
     *
     * @param  array $scripts
     * @return void
     */
    public function runPending(array $scripts)
    {
        // First we will just make sure that there are any scripts to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the scripts have been run against this database system.
        if (count($scripts) == 0) {
            $this->note('<info>Nothing to run.</info>');

            return;
        }

        // Once we have the array of scripts, we will spin through them and run the
        // scripts "run" so the changes are made to the databases. We'll then log
        // that the scripts was run so we don't repeat it next time we execute.
        foreach ($scripts as $file) {
            $this->runUp($file);
        }
    }

    /**
     * Run "run" a script instance.
     *
     * @param  string $file
     * @return void
     */
    protected function runUp($file)
    {
        // First we will resolve a "real" instance of the script class from this
        // script file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $script = $this->resolve(
            $name = $this->getScriptName($file)
        );

        $this->note("<comment>Running:</comment> {$name}");

        if (! $this->runScript($script, 'run')) return;

        // Once we have run a scripts class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a script
        // in the application.
        $this->scriptsRepository->log($name);

        $this->note("<info>Ran:</info>  {$name}");
    }

    /**
     * ToDo: Run a script inside a transaction if the database supports it.
     *
     * @param  object $script
     * @param  string $method
     * @return bool
     */
    protected function runScript($script, $method): bool
    {
        if (! method_exists($script, $method)) {
            $scriptName = get_class($script);
            $this->note("<error>The script {$scriptName} have not method - '$method'. The running not completed.</error>");
            return false;
        }

        $script->{$method}();

        return true;
    }

    /**
     * Resolve a script instance from a file.
     *
     * @param  string $file
     * @return object
     */
    public function resolve($file)
    {
        $class = Str::studly(implode('_', array_slice(explode('_', $file), 4)));

        return new $class;
    }

    /**
     * Get the scripts files that have not yet run.
     *
     * @param  array $files
     * @param  array $ran
     * @return array
     */
    protected function pendingScripts($files, $ran)
    {
        return Collection::make($files)
            ->reject(function ($file) use ($ran) {
                return in_array($this->getScriptName($file), $ran);
            })->values()->all();
    }

    public function getScriptsFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return $this->files->glob($path . '/*_*.php');
        })->filter()->sortBy(function ($file) {
            return $this->getScriptName($file);
        })->values()->keyBy(function ($file) {
            return $this->getScriptName($file);
        })->all();
    }

    /**
     * Get the name of the script.
     *
     * @param  string $path
     * @return string
     */
    public function getScriptName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Raise a note event for the service.
     *
     * @param  string $message
     * @return void
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
