<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assessment;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class AssessmentService extends BaseService
{
    public function __construct(Assessment $subject)
    {
        $this->model = $subject;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): Assessment
    {
        //{
        //"name": "Montana Rice",
        //"subjectId": 2,
        //"description": "Corporis ut dolore a",
        //"duration": 96,
        //"passMarks": 0,
        //"totalMarks": 0,
        //"maxAttempts": 10,
        //"validFrom": "2023-12-28T02:54:28.614Z",
        //"validTo": "2023-12-28T04:54:28.614Z",
        //"isPublished": true,
        //"questions": [
        //{
        //"id": 1,
        //"mark": 0
        //},
        //{
        //    "id": 2,
        //            "mark": 0
        //        },
        //{
        //    "id": 3,
        //            "mark": 0
        //        },
        //{
        //    "id": 4,
        //            "mark": 0
        //        },
        //{
        //    "id": 5,
        //            "mark": 0
        //        }
        //],
        //"groupIds": [
        //    1,
        //    2
        //]
        //}
        try {
            DB::beginTransaction();

            $assessment = $this->model->create([
                'name' => $data['name'],
                'subject_id' => $data['subjectId'],
                'description' => $data['description'],
                'duration' => $data['duration'],
                'pass_marks' => $data['passMarks'],
                'total_marks' => $data['totalMarks'],
                'max_attempts' => $data['maxAttempts'],
                'valid_from' => $data['validFrom'] ? (new DateTime($data['validFrom']))->format('Y-m-d H:i:s') : null,
                'valid_to' => $data['validTo'] ? (new DateTime($data['validTo']))->format('Y-m-d H:i:s') : null,
                'is_published' => $data['isPublished'],
            ]);

            foreach ($data['questions'] as $question) {
                $assessment->questions()->attach($question['id'], ['marks' => $question['marks'], 'order' => $question['order']]);
            }

            foreach ($data['groupIds'] as $groupId) {
                $assessment->groups()->attach($groupId);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $assessment;
    }
}
