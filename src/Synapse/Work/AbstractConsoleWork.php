<?php

namespace Synapse\Work;

use Synapse\Application;
use Synapse\ApplicationInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * An abstract class for wrapping console commands as Work to be performed by workers
 *
 * Simply extend this class and overload getConsoleCommand() to return the correct service from $app
 */
abstract class AbstractConsoleWork
{
    /**
     * Manually load, configure, and run the console command
     *
     * Inject $this->args as Input object arguments
     */
    public function perform()
    {
        $app = $this->application();

        $command = $this->getConsoleCommand($app);

        $inputDefinition = $this->getInputDefinition($this->args);

        // Create Input object with $this->args loaded as Input arguments
        $input  = new ArrayInput($this->args, $inputDefinition);
        $output = new ConsoleOutput;

        // Output error details to the console if available
        try {
            $command->execute($input, $output);
        } catch (\Exception $e) {
            $output->writeln('Exception: '.$e->getMessage());
            $output->writeln('Code: '.$e->getCode());
            $output->writeln('Stack trace: '.$e->getTraceAsString());

            throw $e;
        }
    }

    /**
     * Return the Silex application loaded with all routes and services
     *
     * @return Application
     */
    protected function application()
    {
        // Initialize the Silex Application
        $applicationInitializer = new ApplicationInitializer;

        $app = $applicationInitializer->initialize();

        // Set the default routes and services
        $defaultRoutes   = new Application\Routes;
        $defaultServices = new Application\Services;

        $defaultRoutes->define($app);
        $defaultServices->register($app);

        // Set the application-specific services
        $appServices = new \Application\Services;

        $appServices->register($app);

        return $app;
    }

    /**
     * Get an InputDefinition formed by the arguments provided
     *
     * Effectively provides a passthrough for any input arguments provided
     *
     * @param  array  $args Arguments
     * @return InputDefinition
     */
    protected function getInputDefinition(array $args)
    {
        $definition = new InputDefinition();

        foreach ($args as $field => $value) {
            $definition->addArgument(new InputArgument($field));
        }

        return $definition;
    }

    /**
     * @param  Application $app
     * @return Command          The console command this job should run
     */
    abstract protected function getConsoleCommand(Application $app);
}
