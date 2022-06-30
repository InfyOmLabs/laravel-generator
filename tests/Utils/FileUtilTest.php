<?php

use InfyOm\Generator\Utils\FileUtil;

it('mocks create file', function () {
    $mock = \Mockery::mock('alias:'.FileUtil::class);

    $mock->shouldReceive('createFile')
        ->withArgs([__DIR__, 'test.php', 'test'])
        ->andReturn(true);

    $result = FileUtil::createFile(__DIR__, 'test.php', 'test');

    $this->assertEquals(true, $result);
});
