<?php

namespace ITBrains\ReleaseScript;

interface ScriptRepositoryInterface
{
    /**
     * Get the run scripts for a give package.
     *
     * @return array
     */
    public function getRan(): array;

    /**
     * Create the script repository data store.
     *
     * @return void
     */
    public function createRepository(): void;

    /**
     * @return bool
     */
    public function repositoryExists(): bool;

    /**
     * Log that a script was run.
     *
     * @param  string  $file
     * @return void
     */
    public function log($file);
}
