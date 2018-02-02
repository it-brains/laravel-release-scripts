<?php

namespace ITBrains\ReleaseScript\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use ITBrains\ReleaseScript\ScriptCreator;

class MakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:release-script {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new release script file';

    /**
     * @var ScriptCreator
     */
    protected $creator;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * Create a new command instance.
     *
     * @param ScriptCreator $creator
     * @param Composer $composer
     * @param Filesystem $file
     */
    public function __construct(ScriptCreator $creator, Composer $composer, Filesystem $file)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->prepareBasePath();

        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate scripts.
        $name = trim($this->argument('name'));

        // Now we are ready to write the script out to disk. Once we've written
        // the script out, we will dump-autoload for the entire framework to
        // make sure that the scripts are registered by the class loaders.
        $this->writeScript($name);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the script file to disk.
     *
     * @param  string $name
     * @return string
     */
    protected function writeScript($name)
    {
        $file = pathinfo($this->creator->create($name, $this->getScriptPath()), PATHINFO_FILENAME);

        $this->line("<info>Created Script:</info> {$file}");
    }

    /**
     * Get script path.
     *
     * @return string
     */
    protected function getScriptPath()
    {
        return parent::getScriptsPath();
    }

    protected function prepareBasePath()
    {
        if (! $this->file->isDirectory($this->getScriptsPath())) {
            $this->file->makeDirectory($this->getScriptsPath());
        }
    }
}
