<?php

namespace ITBrains\ReleaseScript\Console;

use Illuminate\Console\Command;

class BaseCommand extends Command
{
    /**
     * Get all of the script paths.
     *
     * @return array
     */
    protected function getScriptsPaths()
    {
        return [$this->getScriptsPath()];
    }

    /**
     * Get the path to the script directory.
     *
     * @return string
     */
    protected function getScriptsPath()
    {
        return $this->laravel->databasePath() . DIRECTORY_SEPARATOR . 'scripts';
    }
}
