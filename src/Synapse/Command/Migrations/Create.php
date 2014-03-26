<?php

namespace Synapse\Command\Migrations;

use Synapse\View\Migration\Create as CreateMigrationView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for creating database migrations. Based on Kohana Minion task-migrations.
 *
 * Example usage:
 *     ./console migrations:create 'Add email field to users table'
 */
class Create extends Command
{
    /**
     * View for new migration files
     *
     * @var Synapse\View\Migration\Create
     */
    protected $newMigrationView;

    /**
     * Set the injected new migration view, call the parent constructor
     *
     * @param Synapse\View\Migration\Create $newMigrationView
     */
    public function __construct(CreateMigrationView $newMigrationView)
    {
        $this->newMigrationView = $newMigrationView;

        parent::__construct();
    }

    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setName('migrations:create')
            ->setDescription('Create a new database migration')
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'Enter a short description of the migration: '
            );
    }

    /**
     * Execute this console command, in order to create a new migration
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $description = $input->getArgument('description');
        $time        = date('YmdHis');
        $classname   = $this->classname($time, $description);
        $filepath    = APPDIR.'/src/Application/Migrations/'.$classname.'.php';

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
     * Get the name of the new migration class.
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
}
