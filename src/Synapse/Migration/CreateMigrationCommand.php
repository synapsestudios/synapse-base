<?php

namespace Synapse\Migration;

use Synapse\Command\CommandInterface;
use Synapse\View\Migration\Create as CreateMigrationView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for creating database migrations. Based on Kohana Minion task-migrations.
 *
 * Example usage:
 *     ./console migrations:create 'Add email field to users table'
 */
class CreateMigrationCommand extends Command
{
    /**
     * View for new migration files
     *
     * @var Synapse\View\Migration\Create
     */
    protected $newMigrationView;

    protected $migrationNamespace = 'Application\\Migrations\\';

    /**
     * Set the injected new migration view, call the parent constructor
     *
     * @param string              $name             Name of the console command
     * @param CreateMigrationView $newMigrationView
     */
    public function __construct(CreateMigrationView $newMigrationView)
    {
        $this->newMigrationView = $newMigrationView;
    }

    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setDescription('Create a new database migration')
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'Enter a short description of the migration: '
            );
    }

    /**
     * Set the namespace for the migrations
     *
     * @param string $namespace
     */
    public function setMigrationNamespace($namespace)
    {
        $this->migrationNamespace = $namespace;
        return $this;
    }

    /**
     * Execute this console command, in order to create a new migration
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $description = $input->getArgument('description');
        $time        = date('YmdHis');
        $classname   = $this->classname($time, $description);
        $filepath    = APPDIR.'/src/'.$this->namespaceToPath().$classname.'.php';

        if (! is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0775, true);
        }

        $view = $this->newMigrationView;

        $view->description($description);
        $view->classname($classname);
        $view->timestamp($time);

        file_put_contents($filepath, (string) $view);

        $message = '  Created migration file '.$filepath;

        // Output message padded by newlines
        $output->write(['', $message, ''], true);
    }

    /**
     * Get the name of the new migration class
     *
     * Converts description to camelCase and appends timestamp.
     * Example:
     *     // From:
     *     Example description of a migration
     *
     *     // To:
     *     ExampleDescriptionOfAMigration20140220001906
     *
     * @param  string $time        Timestamp
     * @param  string $description User-provided description of new migration
     * @return string
     */
    protected function classname($time, $description)
    {
        $description = substr(strtolower($description), 0, 30);
        $description = ucwords($description);
        $description = preg_replace('/[^a-zA-Z]+/', '', $description);
        return $description.$time;
    }

    /**
     * Returns the path to the migration namespace
     *
     * @return string
     */
    protected function namespaceToPath()
    {
        return str_replace('\\', '/', $this->migrationNamespace);
    }
}
