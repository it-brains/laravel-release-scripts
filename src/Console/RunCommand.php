<?php

namespace ITBrains\ReleaseScript\Console;

use Illuminate\Console\ConfirmableTrait;
use ITBrains\ReleaseScript\DatabaseScriptRepository;
use ITBrains\ReleaseScript\ScriptRepositoryInterface;
use ITBrains\ReleaseScript\ScriptService;

class RunCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release-script:run 
                {--force : Force the operation to run when in production.}
                {--migrate : Indicates if the migrate task should be run before.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the one time execute release scripts';

    /**
     * @var DatabaseScriptRepository
     */
    protected $scriptsRepository;

    /**
     * @var ScriptService
     */
    protected $scriptService;

    /**
     * Create a new command instance.
     *
     * @param ScriptRepositoryInterface $repository
     * @param ScriptService $scriptService
     */
    public function __construct(ScriptRepositoryInterface $repository, ScriptService $scriptService)
    {
        parent::__construct();

        $this->scriptsRepository = $repository;
        $this->scriptService = $scriptService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        // If the "migrate" option has been given then we will run the 'migrate' command
        if($this->option('migrate')) {
            $this->call('migrate', ['--force' => true]);
        }

        $this->prepareDatabase();

        $this->scriptService->run($this->getScriptsPaths());

        // Once the 'script service' has run we will grab the note output and send it out to
        // the console screen, since the 'script service' itself functions without having
        // any instances of the OutputInterface contract passed into the class.
        foreach ($this->scriptService->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

    /**
     * Prepare the scripts database for running.
     *
     * @return void
     */
    private function prepareDatabase()
    {
        if (! $this->scriptsRepository->repositoryExists()) {
            $this->call('release-script:install');
        }
    }
}
