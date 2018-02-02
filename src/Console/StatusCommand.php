<?php

namespace ITBrains\ReleaseScript\Console;

use Illuminate\Support\Collection;
use ITBrains\ReleaseScript\ScriptRepositoryInterface;
use ITBrains\ReleaseScript\ScriptService;

class StatusCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release-script:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of each release script';

    /**
     * @var ScriptService
     */
    protected $scriptService;

    /**
     * @var ScriptRepositoryInterface
     */
    protected $scriptsRepository;

    /**
     * @param ScriptRepositoryInterface $repository
     * @param ScriptService $scriptService
     */
    public function __construct(ScriptRepositoryInterface $repository, ScriptService $scriptService)
    {
        parent::__construct();

        $this->scriptService = $scriptService;
        $this->scriptsRepository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->scriptsRepository->repositoryExists()) {
            return $this->error('No scripts found.');
        }

        $ran = $this->scriptsRepository->getRan();

        if (count($scripts = $this->getStatusFor($ran)) > 0) {
            $this->table(['Ran?', 'Script'], $scripts);
        } else {
            $this->error('No scripts found');
        }
    }

    /**
     * Get the status for the given ran scripts.
     *
     * @param  array  $ran
     * @return \Illuminate\Support\Collection
     */
    protected function getStatusFor(array $ran)
    {
        return Collection::make($this->getAllScriptFiles())
            ->map(function ($script) use ($ran) {
                $scriptName = $this->scriptService->getScriptName($script);

                return in_array($scriptName, $ran)
                    ? ['<info>Y</info>', $scriptName]
                    : ['<fg=red>N</fg=red>', $scriptName];
            });
    }

    /**
     * Get an array of all of the script files.
     *
     * @return array
     */
    protected function getAllScriptFiles()
    {
        return $this->scriptService->getScriptsFiles($this->getScriptsPaths());
    }
}
