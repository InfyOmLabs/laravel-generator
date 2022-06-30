<?php

use InfyOm\Generator\Commands\RollbackGeneratorCommand;
use function Pest\Laravel\artisan;

it('fails with invalid rollback type', function () {
    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'random'])
        ->assertExitCode(1);
});
