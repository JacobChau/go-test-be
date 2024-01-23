<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Enums\QuestionType;
use App\Models\Media;
use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedToeicQuestions();
    }

    private function seedToeicQuestions(): void
    {
        $category = QuestionCategory::create([
            'name' => 'English',
            'description' => 'English questions',
        ]);

        $userId = User::all()->random()->id;
        $passage = Passage::create([
            'content' => 'Read the following passage and answer the questions that follow.',
        ]);

        $question1 = Question::create([
            'content' => 'What is the main topic of the passage?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question1->options()->createMany([
            [
                'answer' => 'The history of the English language',
                'is_correct' => false,
            ],
            [
                'answer' => 'The history of the TOEIC test',
                'is_correct' => false,
            ],
            [
                'answer' => 'The history of the TOEIC test in Japan',
                'is_correct' => true,
            ],
            [
                'answer' => 'The history of the TOEIC test in the United States',
                'is_correct' => false,
            ],
        ]);

        $question2 = Question::create([
            'content' => 'What is the TOEIC test?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question2->options()->createMany([
            [
                'answer' => 'A test of English for international communication',
                'is_correct' => true,
            ],
            [
                'answer' => 'A test of English for international companies',
                'is_correct' => false,
            ],
            [
                'answer' => 'A test of English for international students',
                'is_correct' => false,
            ],
            [
                'answer' => 'A test of English for international travel',
                'is_correct' => false,
            ],
        ]);

        $question3 = Question::create([
            'content' => 'When was the TOEIC test first administered?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question3->options()->createMany([
            [
                'answer' => 'In 1979',
                'is_correct' => false,
            ],
            [
                'answer' => 'In 1977',
                'is_correct' => true,
            ],
            [
                'answer' => 'In 1975',
                'is_correct' => false,
            ],
            [
                'answer' => 'In 1973',
                'is_correct' => false,
            ],
        ]);

        $question4 = Question::create([
            'content' => 'What is the TOEIC test used for?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question4->options()->createMany([
            [
                'answer' => 'To measure English proficiency',
                'is_correct' => true,
            ],
            [
                'answer' => 'To measure English vocabulary',
                'is_correct' => false,
            ],
            [
                'answer' => 'To measure English grammar',
                'is_correct' => false,
            ],
            [
                'answer' => 'To measure English reading comprehension',
                'is_correct' => false,
            ],
        ]);

        $question5 = Question::create([
            'content' => 'What is the TOEIC test used for?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question5->options()->createMany([
            [
                'answer' => 'To measure English proficiency',
                'is_correct' => true,
            ],
            [
                'answer' => 'To measure English vocabulary',
                'is_correct' => false,
            ],
            [
                'answer' => 'To measure English grammar',
                'is_correct' => false,
            ],
            [
                'answer' => 'To measure English reading comprehension',
                'is_correct' => false,
            ],
        ]);

        $question6 = Question::create([
            'content' => 'The _______ of the TOEIC test is to measure English proficiency.',
            'type' => QuestionType::FillIn,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion6 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);

        $question6->media()->save($mediaForQuestion6);

        $question6->options()->create([
            'answer' => 'purpose',
            'is_correct' => true,
        ]);

        $question7 = Question::create([
            'content' => 'The TOEIC test is administered by ___.',
            'type' => QuestionType::FillIn,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question7->media()->save($mediaForQuestion6);

        $question7->options()->create([
            'answer' => 'ETS',
            'is_correct' => true,
        ]);

        $question8 = Question::create([
            'content' => 'The rate of growth of the TOEIC test in Japan is ___.',
            'type' => QuestionType::FillIn,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question8->media()->save($mediaForQuestion6);

        $question8->options()->create([
            'answer' => 'rapid',
            'is_correct' => true,
        ]);

        $question8->explanation()->create([
            'content' => 'The rate of growth of the TOEIC test in Japan is rapid.',
        ]);

        $question9 = Question::create([
            'content' => 'The rapid _________ of technology has transformed various aspects of our lives.',
            'type' => QuestionType::FillIn,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question9->media()->save($mediaForQuestion6);

        $question9->options()->create([
            'answer' => 'advancement',
            'is_correct' => true,
        ]);

        $mediaForExplanationQuestion9 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);
        $question9->explanation()->create([
            'content' => 'The rapid advancement of technology has transformed various aspects of our lives.',
        ]);

        $question9->explanation->media()->save($mediaForExplanationQuestion9);

        $question10 = Question::create([
            'content' => 'The TOEIC test is used by more than 14,000 companies in ___ countries.',
            'type' => QuestionType::FillIn,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion10 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);

        $question10->media()->save($mediaForQuestion10);

        $question10->options()->create([
            'answer' => '150',
            'is_correct' => true,
        ]);

        $passage = Passage::create([
            'content' => 'Read the following passage and provide a short answer.',
        ]);

        $question11 = Question::create([
            'content' => 'What is the main topic of the passage?',
            'type' => QuestionType::Text,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question12 = Question::create([
            'content' => 'What is the TOEIC test?',
            'type' => QuestionType::Text,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question13 = Question::create([
            'content' => 'When was the TOEIC test first administered?',
            'type' => QuestionType::Text,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question14 = Question::create([
            'content' => 'What is the TOEIC test used for?',
            'type' => QuestionType::Text,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question15 = Question::create([
            'content' => 'What is the main topic of the passage?',
            'type' => QuestionType::Text,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $passage = Passage::create([
            'content' => 'Read the following passage and answer the questions that follow (can be multiple answers).',
        ]);

        $question16 = Question::create([
            'content' => 'What are the advantages of using renewable energy sources?',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question16->options()->createMany([
            [
                'answer' => 'They are cheaper than fossil fuels.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are more environmentally friendly than fossil fuels.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are more efficient than fossil fuels.',
                'is_correct' => false,
            ],
            [
                'answer' => 'They are more reliable than fossil fuels.',
                'is_correct' => false,
            ],
        ]);

        $question16->explanation()->create([
            'content' => 'The advantages of using renewable energy sources are that they are cheaper than fossil fuels and more environmentally friendly than fossil fuels.',
        ]);

        $question17 = Question::create([
            'content' => 'What are the disadvantages of using renewable energy sources?',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question17->options()->createMany([
            [
                'answer' => 'They are more expensive than fossil fuels.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are less environmentally friendly than fossil fuels.',
                'is_correct' => false,
            ],
            [
                'answer' => 'They are less efficient than fossil fuels.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are less reliable than fossil fuels.',
                'is_correct' => true,
            ],
        ]);

        $question17->explanation()->create([
            'content' => 'The disadvantages of using renewable energy sources are that they are more expensive than fossil fuels, less efficient than fossil fuels, and less reliable than fossil fuels.',
        ]);

        $question18 = Question::create([
            'content' => 'What are the advantages of using fossil fuels?',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question18->options()->createMany([
            [
                'answer' => 'They are cheaper than renewable energy sources.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are more environmentally friendly than renewable energy sources.',
                'is_correct' => false,
            ],
            [
                'answer' => 'They are more efficient than renewable energy sources.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are more reliable than renewable energy sources.',
                'is_correct' => true,
            ],

        ]);

        $question19 = Question::create([
            'content' => 'What are the disadvantages of using fossil fuels?',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question19->explanation()->create([
            'content' => 'The disadvantages of using fossil fuels are that they are more expensive than renewable energy sources, less efficient than renewable energy sources, and less reliable than renewable energy sources.',
        ]);

        $question19->options()->createMany([
            [
                'answer' => 'They are more expensive than renewable energy sources.',
                'is_correct' => false,
            ],
            [
                'answer' => 'They are less environmentally friendly than renewable energy sources.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are less efficient than renewable energy sources.',
                'is_correct' => false,
            ],
            [
                'answer' => 'They are less reliable than renewable energy sources.',
                'is_correct' => false,
            ],
        ]);

        $question20 = Question::create([
            'content' => 'What are the qualities of a good leader?',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $question20->options()->createMany([
            [
                'answer' => 'They are charismatic.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are intelligent.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are honest.',
                'is_correct' => true,
            ],
            [
                'answer' => 'They are humble.',
                'is_correct' => false,
            ],
        ]);

        $question20->explanation()->create([
            'content' => 'The qualities of a good leader are that they are charismatic, intelligent, and honest.',
        ]);

        $passage = Passage::create([
            'content' => 'Look at the following image and pick the correct answer which best describes the image (can be multiple answers).',
        ]);

        $question21 = Question::create([
            'content' => 'Choose the images that best describe the dog.',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $optionsDataQuestion21 = [
            [
                'is_correct' => true,
            ],
            [
                'is_correct' => true,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
        ];

        $optionsQuestion21 = $question21->options()->createMany($optionsDataQuestion21);

        $mediaUrlsQuestion21 = [
            'https://www.ets.org/toeic/images/dog2.jpg',
            'https://www.ets.org/toeic/images/dog.jpg',
            'https://www.ets.org/toeic/images/cat.jpg',
            'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
        ];

        foreach ($optionsQuestion21 as $index => $option) {
            $media = new Media([
                'url' => $mediaUrlsQuestion21[$index],
                'type' => MediaType::Image,
            ]);
            $option->media()->save($media);
        }

        $question22 = Question::create([
            'content' => 'Choose the images that best describe the cat.',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $optionsDataQuestion22 = [
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => true,
            ],
            [
                'is_correct' => false,
            ],
        ];

        $optionsQuestion22 = $question22->options()->createMany($optionsDataQuestion22);

        $mediaUrlsQuestion22 = [
            'https://www.ets.org/toeic/images/dog2.jpg',
            'https://www.ets.org/toeic/images/dog.jpg',
            'https://www.ets.org/toeic/images/cat.jpg',
            'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
        ];

        foreach ($optionsQuestion22 as $index => $option) {
            $media = new Media([
                'url' => $mediaUrlsQuestion22[$index],
                'type' => MediaType::Image,
            ]);
            $option->media()->save($media);
        }

        $question23 = Question::create([
            'content' => 'Choose the images that best describe the house.',
            'type' => QuestionType::MultipleAnswer,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $optionsDataQuestion23 = [
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => true,
            ],
        ];

        $optionsQuestion23 = $question23->options()->createMany($optionsDataQuestion23);

        $mediaUrlsQuestion23 = [
            'https://www.ets.org/toeic/images/dog2.jpg',
            'https://www.ets.org/toeic/images/dog.jpg',
            'https://www.ets.org/toeic/images/cat.jpg',
            'https://www.ets.org/toeic/images/house.jpg',
        ];

        foreach ($optionsQuestion23 as $index => $option) {
            $media = new Media([
                'url' => $mediaUrlsQuestion23[$index],
                'type' => MediaType::Image,
            ]);
            $option->media()->save($media);
        }

        $question24 = Question::create([
            'content' => 'Choose the images that best describe the car.',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $optionsDataQuestion24 = [
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => false,
            ],
            [
                'is_correct' => true,
            ],
        ];

        $optionsQuestion24 = $question24->options()->createMany($optionsDataQuestion24);

        $mediaUrlsQuestion24 = [
            'https://www.ets.org/toeic/images/dog2.jpg',
            'https://www.ets.org/toeic/images/dog.jpg',
            'https://www.ets.org/toeic/images/cat.jpg',
            'https://www.ets.org/toeic/images/car.jpg',
        ];

        foreach ($optionsQuestion24 as $index => $option) {
            $media = new Media([
                'url' => $mediaUrlsQuestion24[$index],
                'type' => MediaType::Image,
            ]);
            $option->media()->save($media);
        }

        $passage = Passage::create([
            'content' => 'Listen to the following audio and answer the questions that follow.',
        ]);

        $question25 = Question::create([
            'content' => 'What is the main topic of the audio?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion25 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);

        $question25->media()->save($mediaForQuestion25);
        $question25->options()->createMany([
            [
                'answer' => 'The weather',
                'is_correct' => false,
            ],
            [
                'answer' => 'The news',
                'is_correct' => false,
            ],
            [
                'answer' => 'The traffic',
                'is_correct' => true,
            ],
            [
                'answer' => 'The sports',
                'is_correct' => false,
            ],
        ]);

        $question26 = Question::create([
            'content' => 'What is the main topic of the audio?',
            'type' => QuestionType::MultipleChoice,
            //            'url' => 'https://www.ets.org/toeic/listening/mp3/Part1_2.mp3',
            //            'type' => MediaType::Audio,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion26 = new Media([
            'url' => 'https://www.ets.org/toeic/listening/mp3/Part1_2.mp3',
            'type' => MediaType::Audio,
        ]);

        $question26->media()->save($mediaForQuestion26);

        $question26->options()->createMany([
            [
                'answer' => 'The weather',
                'is_correct' => false,
            ],
            [
                'answer' => 'The news',
                'is_correct' => true,
            ],
            [
                'answer' => 'The traffic',
                'is_correct' => false,
            ],
            [
                'answer' => 'The sports',
                'is_correct' => false,
            ],
        ]);

        $question27 = Question::create([
            'content' => 'What is he going to do?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion27 = new Media([
            'url' => 'https://www.ets.org/toeic/listening/mp3/Part1_3.mp3',
            'type' => MediaType::Audio,
        ]);

        $question27->media()->save($mediaForQuestion27);

        $question27->options()->createMany([
            [
                'answer' => 'Go to the bank',
                'is_correct' => false,
            ],
            [
                'answer' => 'Go to the post office',
                'is_correct' => true,
            ],
            [
                'answer' => 'Go to the supermarket',
                'is_correct' => false,
            ],
            [
                'answer' => 'Go to the library',
                'is_correct' => false,
            ],
        ]);

        $passage = Passage::create([
            'content' => 'Watch the following video and answer the questions that follow.',
        ]);

        $question28 = Question::create([
            'content' => 'What is the main topic of the video?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion28 = new Media([
            'url' => 'https://www.ets.org/toeic/videos/Part1_1.mp4',
            'type' => MediaType::Video,
        ]);

        $question28->media()->save($mediaForQuestion28);

        $question28->options()->createMany([
            [
                'answer' => 'The weather',
                'is_correct' => false,
            ],
            [
                'answer' => 'The news',
                'is_correct' => false,
            ],
            [
                'answer' => 'The traffic',
                'is_correct' => true,
            ],
            [
                'answer' => 'The sports',
                'is_correct' => false,
            ],
        ]);

        $question29 = Question::create([
            'content' => 'What is the main topic of the video?',
            'type' => QuestionType::MultipleChoice,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion29 = new Media([
            'url' => 'https://www.ets.org/toeic/videos/Part1_2.mp4',
            'type' => MediaType::Video,
        ]);

        $question29->media()->save($mediaForQuestion29);

        $question29->options()->createMany([
            [
                'answer' => 'The weather',
                'is_correct' => false,
            ],
            [
                'answer' => 'The news',
                'is_correct' => true,
            ],
            [
                'answer' => 'The traffic',
                'is_correct' => false,
            ],
            [
                'answer' => 'The sports',
                'is_correct' => false,
            ],
        ]);

        $passage = Passage::create([
            'content' => 'Choose the right statement the question true or false.',
        ]);

        $question30 = Question::create([
            'content' => 'The TOEIC test is used by more than 14,000 companies in 2 countries.',
            'type' => QuestionType::TrueFalse,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion30 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);

        $question30->media()->save($mediaForQuestion30);

        $question30->options()->createMany([
            [
                'answer' => 'True',
                'is_correct' => false,
            ],
            [
                'answer' => 'False',
                'is_correct' => true,
            ],
        ]);

        $question30->explanation()->create([
            'content' => 'The TOEIC test is used by more than 14,000 companies in 150 countries.',
        ]);

        $question31 = Question::create([
            'content' => 'The TOEIC test is used by more than 14,000 companies in 150 countries.',
            'type' => QuestionType::TrueFalse,
            'is_published' => true,
            'passage_id' => $passage->id,
            'category_id' => $category->id,
            'created_by' => $userId,
        ]);

        $mediaForQuestion31 = new Media([
            'url' => 'https://www.ets.org/toeic/images/TOEIC_Logo_Stacked_White.png',
            'type' => MediaType::Image,
        ]);

        $question31->media()->save($mediaForQuestion31);

        $question31->options()->createMany([
            [
                'answer' => 'True',
                'is_correct' => true,
            ],
            [
                'answer' => 'False',
                'is_correct' => false,
            ],
        ]);

        $question31->explanation()->create([
            'content' => 'The TOEIC test is used by more than 14,000 companies in 150 countries.',
        ]);
    }
}
