<?php

namespace Synapse\Install;

use Synapse\Command\CommandInterface;
use Synapse\Stdlib\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for creating database install files. (DbData, DbStructure.) Based on Kohana Minion task-upgrade.
 */
class GenerateInstallCommand extends Command
{
    /**
     * Filename of the database structure install file
     */
    const STRUCTURE_FILE = 'DbStructure.sql';

    /**
     * Filename of the database data install file
     */
    const DATA_FILE = 'DbData.sql';

    /**
     * Database config
     *
     * @var array
     */
    protected $dbConfig;

    /**
     * Install config
     *
     * @var array
     */
    protected $installConfig;

    /**
     * Root namespace of upgrade classes
     *
     * @var string
     */
    protected $upgradeNamespace = 'Application\\Upgrades\\';

    /**
     * Set database config property
     *
     * @param array $dbConfig
     */
    public function setDbConfig(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
    }

    /**
     * Set install config property
     *
     * @param array $installConfig
     */
    public function setInstallConfig(array $installConfig)
    {
        $this->installConfig = $installConfig;
    }

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
     * Return the upgrade namespace
     *
     * @return string
     */
    public function getUpgradeNamespace()
    {
        return $this->upgradeNamespace;
    }

    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setDescription('Generate database install files to match the current database');
    }

    /**
     * Execute this console command, in order to generate install files
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputPath = DATADIR;

        $output->write(['', '  Generating install files...', ''], true);

        $this->dumpStructure($outputPath);
        $output->writeln('  Exported DB structure');

        $this->dumpData($outputPath);
        $output->write(['  Exported DB data', ''], true);
    }

    /**
     * Return the mysqldump command for structure only
     *
     * @param  string $database   the database name
     * @param  string $username   the database username
     * @param  string $password   the database password
     * @param  string $outputPath the resultant file location
     * @return string             the command to run
     */
    public function getDumpStructureCommand($database, $username, $password, $outputPath)
    {
        return sprintf(
            'mysqldump %s -u %s -p%s --no-data | sed "s/AUTO_INCREMENT=[0-9]*//" > %s',
            escapeshellarg($database),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($outputPath)
        );
    }

    /**
     * Return the mysqldump command for data only
     *
     * @param  string $database   the database name
     * @param  string $username   the database username
     * @param  string $password   the database password
     * @param  string $outputPath the resultant file location
     * @param  array  $tables     the tables to include (optional)
     * @return string             the command to run
     */
    public function getDumpDataCommand($database, $username, $password, $outputPath, $tables = array())
    {
        $tables = array_map('escapeshellarg', $tables);

        $command = sprintf(
            'mysqldump %s %s -u %s -p%s --no-create-info > %s',
            escapeshellarg($database),
            implode(' ', $tables),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($outputPath)
        );

        return $command;
    }

    /**
     * Export database structure to dbStructure install file
     *
     * @param  string $outputPath Path where file should be exported
     */
    protected function dumpStructure($outputPath)
    {
        return shell_exec($this->getDumpStructureCommand(
            $this->dbConfig['database'],
            $this->dbConfig['username'],
            $this->dbConfig['password'],
            $outputPath.self::STRUCTURE_FILE
        ));
    }

    /**
     * Export database data to dbData install file
     *
     * @param  string $outputPath Path where file should be exported
     */
    protected function dumpData($outputPath)
    {
        $tables = Arr::get($this->installConfig, 'dataTables', []);

        /**
         *  Do not attempt to create an empty data install file if no tables are to be exported.
         *  Otherwise all tables will be exported.
         */
        if (! count($tables)) {
            return;
        }

        $command = sprintf(
            'mysqldump %s %s -u %s -p%s --no-create-info > %s',
            escapeshellarg($this->dbConfig['database']),
            implode(' ', $tables),
            escapeshellarg($this->dbConfig['username']),
            escapeshellarg($this->dbConfig['password']),
            escapeshellarg($outputPath.self::DATA_FILE)
        );

        return shell_exec($command);
    }
}
