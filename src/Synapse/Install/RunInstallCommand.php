<?php

namespace Synapse\Install;

use Synapse\Command\AbstractDatabaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use SplFileObject;
use RuntimeException;

/**
 * Console command to initially install the app.
 */
class RunInstallCommand extends AbstractDatabaseCommand
{
    /**
     * Root namespace of install classes
     *
     * @var string
     */
    protected $installNamespace = 'Application\Install\\';

    /**
     * Location of install script
     *
     * @var string
     */
    protected $installScript;

    /**
     * Current version of the application
     *
     * @var string
     */
    protected $appVersion;

    /**
     * Current environment of the application
     *
     * @var string
     */
    protected $appEnv;

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
     * Inject the root namespace of install class
     *
     * @param string $installNamespace
     */
    public function setUpgradeNamespace($installNamespace)
    {
        $this->installNamespace = $installNamespace;
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
     * Set the current app environment
     *
     * @param string $env
     */
    public function setAppEnv($env)
    {
        $this->appEnv = $env;
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
     * Set the install script.  If not set, a default will be used.
     *
     * @param AbstractInstall $installScript
     */
    public function setInstallScript(AbstractInstall $installScript)
    {
        $this->installScript = $installScript;
    }

    /**
     * Get install script
     *
     * Use injected script if it exist, otherwise instantiate the default.
     *
     * @return AbstractInstall
     * @throws RuntimeException If the install class cannot be found
     */
    public function getInstallScript()
    {
        if (! $this->installScript) {
            $installClass = $this->installNamespace.'Install';

            if (! class_exists($installClass)) {
                $message = sprintf('No install class found at %s. Nothing to do.', $installClass);

                throw new RuntimeException($message);
            }

            $installScript = new $installClass;

            $this->installScript = $installScript;
        }

        return $this->installScript;
    }

    /**
     * Execute this console command
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dropTables = $input->getOption('drop-tables');

        if (! $this->hasTables() || $dropTables) {
            // Throw an error if dropTables is set in production env
            if ($dropTables && $this->appEnv === 'production') {
                $output->writeln('Cannot drop tables when app environment is production.');
                return;
            }

            // Console message heading padded by a newline
            $output->write(['', '  -- APP INSTALL --', ''], true);

            $output->write(['  Dropping tables', ''], true);
            $this->dropTables();

            try {
                $installScript = $this->getInstallScript();
            } catch (RuntimeException $e) {
                $output->writeln($e->getMessage());
                return;
            }

            $this->install(
                $installScript,
                $output
            );
        }

        // Run all migrations
        $output->writeln('  Executing new migrations');
        $this->runMigrationsCommand->run(new ArrayInput(['migrations:run']), $output);

        $output->write([sprintf('  Done!', $this->appVersion), ''], true);
    }

    /**
     * Checks for existing tables in the database
     *
     * @return boolean Whether or not there are existing tables
     */
    protected function hasTables()
    {
        $tables = $this->db->query('SHOW TABLES', DbAdapter::QUERY_MODE_EXECUTE);

        return (bool) count($tables);
    }

    /**
     * Drop all tables from the database
     */
    protected function dropTables()
    {
        $tables = $this->db->query('SHOW TABLES', DbAdapter::QUERY_MODE_EXECUTE);

        // Disable foreign key checks -- we are wiping the database on purpose
        $this->db->query(
            'SET FOREIGN_KEY_CHECKS = 0',
            DbAdapter::QUERY_MODE_EXECUTE
        );

        foreach ($tables as $table) {
            $this->db->query(
                'DROP TABLE '.reset($table),
                DbAdapter::QUERY_MODE_EXECUTE
            );
        }

        // Re-enable foreign key checks
        $this->db->query(
            'SET FOREIGN_KEY_CHECKS = 1',
            DbAdapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * Install fresh version of the database from db_structure and db_data files
     *
     * @param  AbstractInstall $installScript
     * @param  OutputInterface $output        Command line output interface
     */
    protected function install(AbstractInstall $installScript, OutputInterface $output)
    {
        $dataPath = DATADIR;

        $output->writeln('  Installing App...');

        // Install the database structure
        $this->runSql(
            $dataPath.DIRECTORY_SEPARATOR.GenerateInstallCommand::STRUCTURE_FILE,
            '  Creating initial database schema',
            sprintf('  Database schema file %s not found', GenerateInstallCommand::STRUCTURE_FILE),
            $output
        );

        // Install the database data
        $this->runSql(
            $dataPath.DIRECTORY_SEPARATOR.GenerateInstallCommand::DATA_FILE,
            '  Inserting initial data',
            sprintf('  Database data file %s not found', GenerateInstallCommand::DATA_FILE),
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
}
