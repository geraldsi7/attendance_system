<?php

namespace Database\Seeders;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach(range(1, 100) as $value){
        
            Student::create([
                'name' => $faker->name,
                'email' => $faker->email,
                'student_id' => 'S' . $faker->randomNumber(7),
                'index_number' => $faker->randomNumber(7),
                'password' => Hash::make('student_portal'),
                'section_id'    => 1,
                'email_verified_at' => Carbon::now()
            ]);
        }

        foreach(range(1, 200) as $value){
        
        Student::create([
            'name' => $faker->name,
            'email' => $faker->email,
            'student_id' => 'S' . $faker->randomNumber(7),
            'index_number' => $faker->randomNumber(7),
            'password' => Hash::make('student_portal'),
            'section_id'    => 2,
            'email_verified_at' => Carbon::now()
        ]);
    }

    foreach(range(1, 130) as $value){
        
        Student::create([
            'name' => $faker->name,
            'email' => $faker->email,
            'student_id' => 'S' . $faker->randomNumber(7),
            'index_number' => $faker->randomNumber(7),
            'password' => Hash::make('student_portal'),
            'section_id'    => 3,
            'email_verified_at' => Carbon::now()
        ]);
    }
    }
}
