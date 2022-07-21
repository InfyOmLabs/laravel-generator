<?php

namespace Tests\Helpers;

it('verifies model name from table names', function () {
    $tableNames = ['posts', 'person_addresses', 'personEmails'];
    $modelNames = ['Post', 'PersonAddress', 'PersonEmail'];

    $i = 0;
    foreach ($tableNames as $tableName) {
        $result = model_name_from_table_name($tableName);
        expect($result)->toBe($modelNames[$i]);
        $i++;
    }
});
