<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Requests\SubmitAssessmentRequest;
use App\Http\Requests\UpdateAnswerAttemptRequest;
use App\Http\Requests\UpdateAssessmentRequest;
use App\Http\Resources\AssessmentDetailResource;
use App\Http\Resources\AssessmentResource;
use App\Http\Resources\QuestionDetailResource;
use App\Models\Assessment;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $assessments = $this->assessmentService->getList(AssessmentResource::class, request()->all());

        return $this->sendResponse($assessments, 'Assessments retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        $assessment = $this->assessmentService->create($request->validated());

        return response()->json([
            'message' => 'Assessment created successfully',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $assessment = $this->assessmentService->getById($id, request()->all());

        return $this->sendResponse(new AssessmentDetailResource($assessment), 'Assessment retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssessmentRequest $request, Assessment $assessment): JsonResponse
    {
        $this->assessmentService->update($assessment->id, $request->validated());

        return $this->sendResponse(null, 'Assessment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->assessmentService->delete($id);

        return $this->sendResponse(null, 'Assessment deleted successfully.');
    }

    public function questions(string $id): JsonResponse
    {
        $questions = $this->assessmentService->getQuestions($id);

        return $this->sendResponse(QuestionDetailResource::collection($questions), 'Questions retrieved successfully.');
    }

    public function attempt(string $id): JsonResponse
    {
        $response = $this->assessmentService->attempt($id);
        $message = $response['message'];
        unset($response['message']);

        return $this->sendResponse($response, $message);
    }

    public function submit(SubmitAssessmentRequest $request, string $id): JsonResponse
    {
        $response = $this->assessmentService->submit($request->validated(), $id);

        return $this->sendResponse($response);
    }

    public function resultDetail(string $assessmentId, string $attemptId): JsonResponse
    {
        $response = $this->assessmentService->resultDetail($assessmentId, $attemptId);

        return $this->sendResponse($response);
    }

    public function results(): JsonResponse
    {
        $response = $this->assessmentService->results();

        return $this->sendResponse($response);
    }

    public function management(): JsonResponse
    {
        $response = $this->assessmentService->management();

        return $this->sendResponse($response);
    }

    public function getResultsByAssessment(string $id): JsonResponse
    {
        $response = $this->assessmentService->getResultsByAssessmentId($id);

        return $this->sendResponse($response);
    }

    public function updateAnswerAttempt(string $assessmentId, string $attemptId, string $id, UpdateAnswerAttemptRequest $request): JsonResponse
    {
        $response = $this->assessmentService->updateAnswerAttempt($assessmentId, $attemptId, $id, $request->validated());

        return $this->sendResponse($response);
    }

    public function publishResult(string $assessmentId, string $attemptId): JsonResponse
    {
        $response = $this->assessmentService->publishResult($assessmentId, $attemptId);

        return $this->sendResponse($response);
    }
}
