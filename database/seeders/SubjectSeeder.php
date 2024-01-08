<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Toeic',
                'description' => 'Test of English for International Communication',
            ],
            [
                'name' => 'Ielts',
                'description' => 'International English Language Testing System',
            ],
            [
                'name' => 'Toefl',
                'description' => 'Test of English as a Foreign Language',
            ],
        ];

        Subject::insert($data);
    }
}
