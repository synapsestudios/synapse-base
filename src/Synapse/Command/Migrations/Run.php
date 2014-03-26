<?php

namespace Synapse\Command\Migrations;

use Synapse\Migration\AbstractMigration;
use Synapse\Command\AbstractDatabaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use DirectoryIterator;
use Exception;
use ArrayObject;

/**
 * Console command to run all new migrations on the database. Based on Kohana Minion task-migrations.
 *
 * Uses the app_migrations table to record which migrations have already been run.
 */
class Run extends AbstractDatabaseCommand
{
    /**
     * Root namespace of migration classes
     *
     * @var string
     */
    protected $migrationNamespace = 'Application\Migrations\\';

    /**
     * Inject the root namespace of migration classes
     *
     * @param string $migrationNamespace
     */
    public function setMigrationNamespace($migrationNamespace)
    {
        $this->migrationNamespace = $migrationNamespace;
    }

    /**
     * Set the console command's name and description
     */
    protected function configure()
    {
        $this->setName('migrations:run')
            ->setDescription('Run all new database migrations');
    }

    /**
     * Execute this console command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createAppMigrationsTable();

        $migrations = $this->migrationsToRun();

        $count = 0;
        foreach ($migrations as $migration) {
            $migration->execute($this->db);

            $output->writeln(sprintf(
                '  * DONE *  %s (%s)',
                $migration->getDescription(),
                $migration->getTimestamp()
            ));

            $this->recordMigration($migration);

            $count++;
        }

        if ($count === 0) {
            $message = '  No new migrations to run';
        } else {
            $message = sprintf('  Executed %d migrations', $count);
        }

        $output->writeln([$message, ''], true);
    }

    /**
     * Create the app_migrations table if it does not exist
     */
    protected function createAppMigrationsTable()
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `app_migrations` (
            `timestamp` VARCHAR(14) NOT NULL,
            `description` VARCHAR(100) NOT NULL)',
            DbAdapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * Determine all migrations that have not yet been run on the database
     *
     * @return array
     */
    protected function migrationsToRun()
    {
        // Get all migration files
        $path = APPDIR.'/src/'.str_replace('\\', '/', $this->migrationNamespace);

        if (! is_dir($path)) {
            return [];
        }

        $dir = new DirectoryIterator($path);

        $migrations = [];
        foreach ($dir as $file) {
            // Ignore directories and dotfiles (e.g. .DS_Store)
            if (! $file->isFile() or substr($file->getBasename(), 0, 1) === '.') {
                continue;
            }

            $class = $this->migrationNamespace.$file->getBasename('.php');

            $migrations[] = new $class;
        }

        $alreadyExecutedMigrations = $this->alreadyExecutedMigrations();

        $migrationsToRun = [];
        foreach ($migrations as $migration) {
            $compare = new ArrayObject([
                'description' => $migration->getDescription(),
                'timestamp'   => $migration->getTimestamp(),
            ]);

            if (in_array($compare, $alreadyExecutedMigrations)) {
                continue;
            }

            $migrationsToRun[] = $migration;
        }

        return $migrationsToRun;
    }

    /**
     * Insert a record into app_migrations to record that this migration was run
     *
     * @param  AbstractMigration $migration
     */
    protected function recordMigration(AbstractMigration $migration)
    {
        $query = 'INSERT INTO `app_migrations` (`timestamp`, `description`) VALUES ("%s", "%s")';
        $query = sprintf($query, $migration->getTimestamp(), $migration->getDescription());

        $this->db->query($query, DbAdapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Return array of already executed migrations.
     *
     * @return array
     */
    protected function alreadyExecutedMigrations()
    {
        $results = $this->db->query(
            'SELECT * FROM `app_migrations`',
            DbAdapter::QUERY_MODE_EXECUTE
        );

        return (array) iterator_to_array($results, true);
    }
}
