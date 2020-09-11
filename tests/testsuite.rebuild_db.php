<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

$kernel = new Kernel('test', true);
$kernel->boot();
$application = new Application($kernel);
$manager =  $application
                ->getKernel()
                ->getContainer()
                ->get('doctrine');
$connection = $manager->getConnection();

$migrationsRowCount = 0;

if ($connection->getSchemaManager()->tryMethod('tablesExist', 'doctrine_migration_versions') === true) {
    $sql = 'SELECT count(*) AS total FROM doctrine_migration_versions';
    $stmt = $connection->prepare($sql);
    $stmt->execute();
    $migrationsRowCount = $stmt->fetch()['total'];
}

if ($migrationsRowCount == 0) {
    $command = new DropDatabaseDoctrineCommand($manager);
    $application->add($command);
    $input = new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--force' => true,
        '--env' => 'test'

    ]);
    $command->run($input, new ConsoleOutput());
    // This stops a bug where Drop Database does not close the handle properly & causes subsequent
    // "database not found" errors.
    if ($connection->isConnected()) {
        $connection->close();
    }

    $command = new CreateDatabaseDoctrineCommand($manager);
    $application->add($command);
    $input = new ArrayInput(array(
        'command' => 'doctrine:database:create',
        '--env' => 'test'
    ));
    $command->run($input, new ConsoleOutput());
}

//old way using MigrationsMigrateDoctrineCommand no longer works ...
$process = new Process(['bin/console', 'doctrine:migrations:migrate', '--env=test', '--no-interaction', '--quiet']);
$process->run();

if (!$process->isSuccessful()) {
    throw new ProcessFailedException($process);
}
