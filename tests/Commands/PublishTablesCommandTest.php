<?php

use InfyOm\Generator\Commands\Publish\PublishTablesCommand;
use function Pest\Laravel\artisan;

it('thrown exceptions with invalid type passed', function () {
    artisan(PublishTablesCommand::class, ['type' => 'invalid']);
})->throws(Exception::class, 'Invalid Table Type');
