<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(\Tests\Models\MyTable::class, function (Faker $faker) {
    return [
        'field1_id' => $faker->randomDigit,
        'field2_id' => $faker->randomDigit,
        'field3_id' => $faker->randomDigit,
    ];
});
