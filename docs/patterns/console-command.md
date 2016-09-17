# Console Command

## What is it?

Console commands run PHP on the command line. They can be used to run recurring tasks on-demand, such as cronjobs or database migrations. We use the [Symfony Console](http://symfony.com/doc/current/components/console/introduction.html) component.

## Running Console Commands

The console is used like this:

`cd ~/path/to/console/script`
`./console foo:bar`

`foo:bar` is the name of the command. We use the pattern `object:verb` as a general convention, but Symfony Console does not enforce any particular format.

## Commands In Synapse Base

[Synapse Base](https://github.com/synapsestudios/synapse-base) defines a set of general purpose console commands.

### email:send [id]

Send an email, providing the specific `id` of the email:

`./console email:send 67`

### install:generate

Generate install files based on the current database.

Generates two files:

1. DbStructure.sql
1. DbData.sql

DbStructure contains SQL for creating the entire database schema. Running `install:generate` will add all of the current schema to DbStructure, including all tables and indices.

DbData contains SQL for inserting data into the database that should be present upon install. `install:generate` looks in the `install` config file for a key named `dataTables`. The contents of every table listed in that array will be dumped into DbData.

### install:run

1. Install the database schema from DbStructure.sql.
1. Install the default database data from DbData.sql.
1. Run any unapplied migrations.

If 1 or more tables already exist in the database, steps 1 and 2 are skipped unless the `--drop-tables` option is provided.

### migrations:create

Create a new migration:

`./console migrations:create 'description goes here'`

Will generate a migration using the new migration template and output the location of the migration to the console:

```
Created migration file /Users...Migrations/DescriptionGoesHere20140902223619.php
```

The file is generated using the description and current timestamp. The blank migration template includes examples to help you get started.

### migrations:run

Run any unapplied migrations. Applied migrations are tracked in the `app_migrations` table.

## Building New Console Commands

Anyone building a new console command should be familiar with the [Symfony Console documentation](http://symfony.com/doc/current/components/console/introduction.html). It contains critical information that will not be duplicated here.

### Command Proxy

We deviate slightly from Symfony's console pattern by adding the concept of a Command Proxy.

Steps to create a new command:

1. Create the Command class.
2. Create a Command Proxy class that is associated with the Command class.
3. Register the proxy with the Silex Application as a service.

The Command Proxy's job is to forward requests to the actual command.

Why do we use Command Proxies? To solve a very specific edge-case: sometimes one command cannot be instantiated until another command is run. Why is this problematic? Because Silex builds each Command and injects its dependencies whenever *any* command is run. (Even when `./console` is run to view all available commands.) Command Proxies prevent the command's dependencies from being resolved until the command is actually run.

## Example

Both the Command and the CommandProxy should be created in the same directory/namespace. The command class name should be suffixed with `Command` and the CommandProxy class name should match it with `Proxy` at the end.

The CommandProxy should extend `Synapse\Command\CommandProxy` and override the `configure` method to set the description, arguments, and options of the command.

The Command should implement `Synapse\Command\CommandInterface` and override the `execute` method to perform the command.

Registering the Command in the ServiceProvider is slightly more involved than registering other classes. First the Command is injected into the CommandProxy. Then the CommandProxy service is registered as a console command at boot-time with `$app->command()`.

### DeleteDatabaseCommandProxy.php

```PHP
<?php

namespace Application\Database;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Synapse\Command\CommandProxy;

class DeleteDatabaseCommandProxy extends CommandProxy
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Delete everything in the database')
            ->addArgument(
                'database',
                InputArgument::REQUIRED,
                'Which database(s) do you want to delete?'
            )
            ->addOption(
                'ignore-foreign-keys',
                'ignore-fk',
                InputOption::VALUE_NONE,
this option to delete tables regardless of foreign key constraints'
            )
            ->addOption(
                'table',
                't',
                InputArgument::VALUE_IS_ARRAY,
                'Delete these tables (deletes all if not specified)'
            );
    }
}
```

### DeleteDatabaseCommand.php

```PHP
<?php

namespace Application\Database;

use Synapse\Command\CommandInterface;

class DeleteDatabaseCommand implements CommandInterface
{
    /**
     * @var Zend\Db\Adapter\Adapter
     */
    protected $db;

    /**
     * @param DbAdapter $db
     */
    public function setDbAdapter(DbAdapter $db)
    {
        $this->db = $db;
    }

    /**
     * Delete a database
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $database          = $input->getArgument('database');
        $ignoreForeignKeys = $input->getOption('ignore-foreign-keys');
        $tables            = $input->getOption('table');

        $output->writeln('Deleting tables in '.$database.' database.');

        $this->db->query('use '.$database);

        if ($ignoreForeginKeys) {
            // SET FOREIGN_KEY_CHECKS = 0
        }

        if (count($tables) === 0) {
            // Get all tables in database and assign to $tables
        }

        foreach ($tables as $table) {
            $output->writeln('Deleting '.$table.' table.');

            // Delete the table
        }
    }
}
```

### DatabaseServiceProvider.php
```php
<?php

namespace Application\Database;

use Silex\Application;
use Silex\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['database.delete-command'] = $app->share(function ($app) {
            $command = new DeleteDatabaseCommand();
            $command->setDbAdapter($app['db']);
            
            return $command;
        });
        
        $app['database.delete-command-proxy'] = $app->share(function ($app) {
            $command = new DeleteDatabaseCommandProxy('database:delete');
            $command->setFactory($app->raw('database.delete-command'))
                ->setApp($app);

            return $command;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app->command('database.delete-command-proxy');
    }
}
```
