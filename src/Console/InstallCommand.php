<?php

namespace ITBrains\ReleaseScript\Console;

use ITBrains\ReleaseScript\ScriptRepositoryInterface;

class InstallCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release-script:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the scripts table';

    /**
     * @var ScriptRepositoryInterface
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @param ScriptRepositoryInterface $repository
     */
    public function __construct(ScriptRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->repository->repositoryExists()) {
            $this->warn('Scripts table already exists.');
            return;
        }

        $this->repository->createRepository();

        $this->info('Scripts table created successfully.');
    }
}
