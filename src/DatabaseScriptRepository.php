<?php

namespace ITBrains\ReleaseScript;

use Illuminate\Database\ConnectionResolverInterface;

class DatabaseScriptRepository implements ScriptRepositoryInterface
{
    /**
     * The name of the scripts table.
     *
     * @var string
     */
    protected $table;

    /**
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * ScriptsRepository constructor.
     *
     * @param ConnectionResolverInterface $resolver
     * @param $table
     */
    public function __construct(ConnectionResolverInterface $resolver, $table)
    {
        $this->resolver = $resolver;
        $this->table = $table;
    }


    /**
     * Create the script repository data store.
     *
     * @return void
     */
    public function createRepository(): void
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            // The scripts table is responsible for keeping track of which of the
            // scripts have actually run for the application. We'll create the
            // table to hold the script file's path as well as the batch ID.
            $table->increments('id');
            $table->string('script');
        });
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->resolver->connection($this->connection);
    }

    /**
     * Set the information source to gather data.
     *
     * @param  string  $name
     * @return void
     */
    public function setSource($name)
    {
        $this->connection = $name;
    }

    /**
     * Determine if the script repository exists.
     *
     * @return bool
     */
    public function repositoryExists(): bool
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        return $schema->hasTable($this->table);
    }

    /**
     * Get the run scripts for a give package.
     *
     * @return array
     */
    public function getRan(): array
    {
        return $this->table()
            ->orderBy('script', 'asc')
            ->pluck('script')
            ->all();
    }

    /**
     * Log that a script was run.
     *
     * @param  string $file
     * @return void
     */
    public function log($file)
    {
        $record = ['script' => $file];

        $this->table()->insert($record);
    }

    /**
     * Get a query builder for the script table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->getConnection()->table($this->table)->useWritePdo();
    }
}