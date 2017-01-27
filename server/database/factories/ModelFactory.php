<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email,
    ];
});

$factory->define(App\Models\File::class, function ($faker) {
    return [
        'original_name' => $faker->name,
        'filename' => $faker->unique()->name,
        'extension' => $faker->fileExtension,
        'mime' => $faker->mimeType,
        'location' => $faker->url,
        'size' => $faker->numberBetween(10000,200000),
        'ip' => $faker->ipv4
    ];
});
