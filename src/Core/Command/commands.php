<?php

use System\Core\Command\Create\Create;
use System\Core\Command\Create\CreateMigration;
use System\Core\Command\Create\CreateModule;
use System\Core\Command\Init;
use System\Core\Command\Migrator;
use System\Core\Command\Runner;
use System\Core\Command\Welcome;

return [
    Runner::getName() => Runner::class,
    Welcome::getName() => Welcome::class,
    Create::getName() => Create::class,
    CreateMigration::getName() => CreateMigration::class,
    CreateModule::getName() => CreateModule::class,
    Migrator::getName() => Migrator::class,
    Init::getName() => Init::class,
];
