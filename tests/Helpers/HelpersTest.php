<?php

namespace Tests\Helpers;
use Tests\TestCase;

/**
 * Class HelpersTest
 * @package Tests\Helpers
 */
class HelpersTest extends TestCase
{
    /** @test */
    public function test_model_name_from_table_name()
    {
        $tableNames = ['posts', 'person_addresses', 'personEmails'];
        $modelNames = ['Post', 'PersonAddress', 'PersonEmail'];

        $i = 0;
        foreach ($tableNames as $tableName) {
            $result = model_name_from_table_name($tableName);
            $this->assertEquals($modelNames[$i], $result);
            $i++;
        }
    }
}
