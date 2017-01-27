<?php

use Illuminate\Database\Seeder;

class FilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\UploadedFile::class)->create([
            'user_id' => '1'
        ]);

        factory(App\Models\UploadedFile::class)->create([
            'user_id' => '2'
        ]);

        factory(App\Models\UploadedFile::class)->create([
            'user_id' => '3'
        ]);
    }
}