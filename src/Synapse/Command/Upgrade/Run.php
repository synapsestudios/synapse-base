<?php

namespace Synapse\Command\Upgrade;

use Synapse\Command\Upgrade\AbstractUpgradeCommand;
use Synapse\Command\Install\Generate;
use Synapse\Upgrade\AbstractUpgrade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use SplFileObject;

/**
 * Console command to run the current database upgrade. Based on Kohana Minion task-upgrade.
 *
 * Runs the upgrade that matches the version of the current codebase, if such an
 * upgrade is actually found and it has not yet been run.
 *
 * Uses the app_versions table to record which version upgrades have been run.
 */
class Run extends AbstractUpgradeCommand
{
    /**
     * Root namespace of upgrade classes
     *
     * @var string
     */
    protected $upgradeNamespace = 'Application\Upgrades\\';

    /**
     * Current version of the application
     *
     * @var string
     */
    protected $appVersion;

    /**
     * Run migrations console command object
     *
     * @var Symfony\Component\Console\Command\Command
     */
    protected $runMigrationsCommand;

    /**
     * Generate install file console command object
     *
     * @var Symfony\Component\Console\Command\Command
     */
    protected $generateInstallCommand;

    /**
     * Inject the root namespace of upgrade classes
     *
     * @param string $upgradeNamespace
     */
    public function setUpgradeNamespace($upgradeNamespace)
    {
        $this->upgradeNamespace = $upgradeNamespace;
    }

    /**
     * Set the current app version
     *
     * @param string $version
     */
    public function setAppVersion($version)
    {
        $this->appVersion = $version;
    }

    /**
     * Set the run migrations console command
     *
     * @param Symfony\Component\Console\Command\Command
     */
    public function setRunMigrationsCommand(Command $command)
    {
        $this->runMigrationsCommand = $command;
    }

    /**
     * Set the generate install file console command
     *
     * @param Symfony\Component\Console\Command\Command
     */
    public function setGenerateInstallCommand(Command $command)
    {
        $this->generateInstallCommand = $command;
    }

    /**
     * Configure this console command
     */
    protected function configure()
    {
        $this->setName('upgrade:run')
            ->setDescription('Run database upgrade for current app version')
            ->addOption(
                'drop-tables',
                null,
                InputOption::VALUE_NONE,
                'If set, all tables will be dropped, and the database rebuilt from the install file.'
            );
    }

    /**
     * Execute this console command
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Console message heading padded by a newline
        $output->write(['', '  -- APP UPGRADE --', ''], true);

        if ($input->getOption('drop-tables')) {
            $output->write(['  Dropping tables', ''], true);
            $this->dropTables();
        }

        $this->createAppVersionsTable();

        if (! $databaseVersion = $this->currentDatabaseVersion()) {
            // Ensure that install class exists
            $upgradeNamespace = $this->generateInstallCommand->getUpgradeNamespace();
            $installClass     = $upgradeNamespace.'Install';

            if (! class_exists($installClass)) {
                $output->writeln(
                    sprintf('No install class found at %s. Nothing to do.', $installClass)
                );

                return;
            }

            $this->install(
                new $installClass,
                $output
            );

            $this->recordUpgrade($this->appVersion);

            // Refresh database version
            $databaseVersion = $this->currentDatabaseVersion();
        }

        // Run all migrations
        $output->writeln('  Executing new migrations before upgrading');
        $this->runMigrationsCommand->execute($input, $output);

        if (version_compare($databaseVersion, $this->appVersion, '>')) {
            $message = 'Database version (%s) is newer than codebase (%s). Exiting.';
            $message = sprintf($message, $databaseVersion, $this->appVersion);

            throw new \Exception($message);
        }

        if ($databaseVersion === $this->appVersion) {
            $message = sprintf(
                '  Database version and codebase version are the same (%s). Nothing to upgrade.',
                $databaseVersion
            );

            $output->write([$message, ''], true);

            return;
        }

        $upgradeFile = $this->currentUpgrade($this->appVersion);

        if ($upgradeFile === false) {
            $message = sprintf('  No upgrade file exists for current app version %s. Exiting.', $this->appVersion);

            $output->write([$message, ''], true);

            return;
        }

        $class = $this->upgradeNamespace.$upgradeFile->getBasename('.php');

        $upgrade = new $class;

        $output->writeln(sprintf(
            '  Upgrading from version %s to version %s',
            $databaseVersion ?: '(empty)',
            $this->appVersion
        ));

        $upgrade->execute($this->db);

        $this->recordUpgrade($this->appVersion);

        $output->write([sprintf('  Done!', $this->appVersion), ''], true);
    }

    /**
     * Drop all tables from the database
     */
    protected function dropTables()
    {
        $tables = $this->db->query('SHOW TABLES', DbAdapter::QUERY_MODE_EXECUTE);

        foreach ($tables as $table) {
            $this->db->query(
                'DROP TABLE '.reset($table),
                DbAdapter::QUERY_MODE_EXECUTE
            );
        }
    }

    /**
     * Create app_versions table if not exists
     */
    protected function createAppVersionsTable()
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `app_versions` (
            `version` VARCHAR(50) NOT NULL,
            `timestamp` VARCHAR(14) NOT NULL,
            KEY `timestamp` (`timestamp`))',
            DbAdapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * Install fresh version of the database from db_structure and db_data files
     *
     * @param  Synapse\Upgrade\AbstractUpgrade $installScript
     * @param  OutputInterface                 $output        Command line output interface
     */
    protected function install(AbstractUpgrade $installScript, OutputInterface $output)
    {
        $dataPath = $this->generateInstallCommand->dataPath();

        $output->writeln('  Installing App...');

        // Install the database structure
        $this->runSql(
            $dataPath.DIRECTORY_SEPARATOR.Generate::STRUCTURE_FILE,
            '  Creating initial database schema',
            sprintf('  Database schema file %s not found', Generate::STRUCTURE_FILE),
            $output
        );

        // Install the database data
        $this->runSql(
            $dataPath.DIRECTORY_SEPARATOR.Generate::DATA_FILE,
            '  Inserting initial data',
            sprintf('  Database data file %s not found', Generate::DATA_FILE),
            $output
        );

        $output->writeln('  Running install script');

        $installScript->execute($this->db);

        $output->write(['  Install completed!', ''], true);
    }

    /**
     * Given a filepath to a SQL file, load it and run the SQL statements inside
     *
     * @param  string $file            Path to SQL file
     * @param  string $message         Message to output to the console if the file exists
     * @param  string $notFoundMessage Message to output to the console if the file does not exist
     */
    protected function runSql($file, $message, $notFoundMessage, $output)
    {
        if (! is_file($file)) {
            $output->writeln($notFoundMessage);

            return;
        }

        $output->writeln($message);

        $dataSql = file_get_contents($file);

        // Split the sql file on new lines and insert into the database one line at a time
        foreach (preg_split('/;\s*\n/', $dataSql) as $command) {
            try {
                $query = $this->db->query($command, DbAdapter::QUERY_MODE_EXECUTE);
            } catch (Database_Exception $e) {
                if ($e->getCode() !== 1065) { // empty query
                    throw $e;
                }
            }
        }
    }

    /**
     * Return an SplFileObject of the current upgrade file, or false if none exists
     *
     * @param  string             $version
     * @return SplFileObject|bool
     */
    protected function currentUpgrade($version)
    {
        $path = APPDIR.'/src/'.str_replace('\\', '/', $this->upgradeNamespace);
        $file = 'Upgrade_'.str_replace('.', '_', $version).'.php';

        if (file_exists($path.$file)) {
            $file = new SplFileObject($path.$file);

            require $file->getPathname();

            return $file;
        }

        return false;
    }

    /**
     * Records the upgrade in the app_versions table.
     *
     * @param  string $version
     */
    protected function recordUpgrade($version)
    {
        $query = 'INSERT INTO `app_versions` (`version`, `timestamp`) VALUES ("%s", "%s")';
        $query = sprintf($query, $version, time());

        $this->db->query($query, DbAdapter::QUERY_MODE_EXECUTE);
    }
}
