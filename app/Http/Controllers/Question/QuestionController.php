<?php

namespace App\Http\Controllers\Question;

use App\Enums\QuestionType;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionDetailResource;
use App\Models\Question;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Services\Question\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    protected QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $questions = $this->questionService->getList(QuestionResource::class, request()->all());

        return $this->sendResponse($questions, 'Questions retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     * @throws Exception
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        $question = $this->questionService->create($request->validated());

        return response()->json([
            'message' => 'Question created successfully',
            'question' => $question,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $relations = ['explanation', 'options', 'passage', 'category'];
        $question = $this->questionService->getById($id, $relations);

        return $this->sendResponse(new QuestionDetailResource($question), 'Question retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     * @throws Exception
     */
    public function update(UpdateQuestionRequest $request, Question $question): JsonResponse
    {
        $this->questionService->update($question->id, $request->validated());

        return $this->sendResponse(null, 'Question updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
