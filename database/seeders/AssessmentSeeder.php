<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $subjectId = Subject::inRandomOrder()->first()?->id;
            if ($subjectId === null) {
                // run subject seeder first
                $this->call(SubjectSeeder::class);
                $subjectId = Subject::inRandomOrder()->first()?->id;
            }

            $assessment = Assessment::create([
                'name' => 'Assessment '.$i,
                'description' => 'Description for Assessment '.$i,
                'total_marks' => 100,
                'pass_marks' => 70,
                'max_attempts' => 3,
                'is_published' => true,
                'thumbnail' => fake()->imageUrl(),
                'duration' => 60,
                'valid_from' => now(),
                'valid_to' => now()->addDays(7),
                'subject_id' => $subjectId,
                'created_by' => User::inRandomOrder()->first()?->id,
            ]);

            // Assuming you have 10 questions
            $questions = Question::inRandomOrder()->limit(10)->get();

            foreach ($questions as $question) {
                $assessment->questions()->attach($question->id, [
                    'marks' => 10,
                ]);
            }
        }
    }
}
