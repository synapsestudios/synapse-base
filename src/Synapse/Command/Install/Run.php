<?php

namespace Synapse\Command\Install;

use Synapse\Command\Install\AbstractInstallCommand;
use Synapse\Command\Install\Generate;
use Synapse\Install\AbstractInstall;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use SplFileObject;

/**
 * Console command to initially install the app.
 */
class Run extends AbstractInstallCommand
{
    /**
     * Root namespace of install classes
     *
     * @var string
     */
    protected $installNamespace = 'Application\Install\\';

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
     * @return AbstractInstall|bool  Return false if install script not set and
     *                               install class not found.
     */
    public function getInstallScript()
    {
        if (! $this->installScript) {
            $installClass = $this->installNamespace.'Install';

            if (! class_exists($installClass)) {
                return false;
            }

            $installScript = new $installClass;

            $this->installScript = $installScript;
        }

        return $this->installScript;
    }

    /**
     * Configure this console command
     */
    protected function configure()
    {
        $this->setName('install:run')
            ->setDescription('Perform fresh install of the app (WARNING: drops tables)');
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
        $output->write(['', '  -- APP INSTALL --', ''], true);

        $output->write(['  Dropping tables', ''], true);
        $this->dropTables();

        $installScript = $this->getInstallScript();

        if (! $installScript) {
            $output->writeln(
                sprintf('No install class found at %s. Nothing to do.', $installClass)
            );
            return;
        }

        $this->install(
            $installScript,
            $output
        );

        // Run all migrations
        $output->writeln('  Executing new migrations before upgrading');
        $this->runMigrationsCommand->execute($input, $output);

        $output->write([sprintf('  Done!', $this->appVersion), ''], true);
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
     * @param  Synapse\Upgrade\AbstractInstall $installScript
     * @param  OutputInterface                 $output        Command line output interface
     */
    protected function install(AbstractInstall $installScript, OutputInterface $output)
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
}
