<?php

namespace ITBrains\ReleaseScript;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ScriptCreator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new script creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new script at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     * @throws \Exception
     */
    public function create($name, $path)
    {
        $this->ensureScriptDoesntAlreadyExist($name);

        // First we will get the stub file for the script, which serves as a type
        // of template for the script. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub();

        $this->files->put(
            $path = $this->getPath($name, $path),
            $this->populateStub($name, $stub)
        );

        return $path;
    }

    /**
     * Ensure that a script with the given name doesn't already exist.
     *
     * @param  string  $name
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureScriptDoesntAlreadyExist($name)
    {
        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the script stub file.
     */
    protected function getStub()
    {
        return $this->files->get($this->stubPath().'/blank.stub');
    }

    /**
     * Populate the place-holders in the script stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string  $table
     * @return string
     */
    protected function populateStub($name, $stub)
    {
        $stub = str_replace('DummyClass', $this->getClassName($name), $stub);

        return $stub;
    }

    /**
     * Get the class name of a script name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }

    /**
     * Get the full path to the script.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    /**
     * Get the date prefix for the script.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}
