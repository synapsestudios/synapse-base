<?php

namespace Synapse\Command\Upgrade;

use Synapse\Command\Upgrade\AbstractUpgradeCommand;
use Synapse\View\Upgrade\Create as CreateUpgradeView;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for creating database upgrades. Based on Kohana Minion task-upgrade.
 *
 * Example usage:
 *     ./console upgrade:create 1.4.1
 */
class Create extends AbstractUpgradeCommand
{
    /**
     * View for new upgrade files
     *
     * @var Synapse\View\Upgrade\Create
     */
    protected $newUpgradeView;

    /**
     * Set the injected new upgrade view, call the parent constructor
     *
     * @param Synapse\View\Upgrade\Create $newUpgradeView
     */
    public function __construct(CreateUpgradeView $newUpgradeView)
    {
        $this->newUpgradeView = $newUpgradeView;

        parent::__construct();
    }

    /**
     * Set name, description, arguments, and options for this console command
     */
    protected function configure()
    {
        $this->setName('upgrade:create')
            ->setDescription('Create a new database upgrade')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Upgrade version'
            );
    }

    /**
     * Execute this console command, in order to create a new upgrade
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version         = $input->getArgument('version');
        $expectedVersion = $this->currentDatabaseVersion();
        $classname       = $this->classname($version);
        $filepath        = APPDIR.'/src/Application/Upgrades/'.$classname.'.php';

        if (! is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0775, true);
        }

        $view = $this->newUpgradeView;

        $view->classname($classname);
        $view->version($version);
        $view->expectedVersion($expectedVersion);

        file_put_contents($filepath, (string) $view);

        $output->writeln('Created upgrade file '.$filepath);
    }

    /**
     * Get the name of the new upgrade class.
     * Removes all non-numeric characters from version string, except periods.
     * Converts periods to underscores, and prefixes with Upgrade_
     *
     * Example:
     *     // From:
     *     3.1.5
     *
     *     // To:
     *     Upgrade_3_1_5
     *
     * @param  string $version The version of this upgrade
     * @return string
     */
    protected function classname($version)
    {
        $version = preg_replace('/[^0-9.]+/', '', $version);
        $version = str_replace('.', '_', $version);

        return 'Upgrade_'.$version;
    }
}
