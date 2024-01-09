<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAssessmentRequest;
use App\Http\Resources\AssessmentDetailResource;
use App\Http\Resources\AssessmentResource;
use App\Http\Resources\QuestionDetailResource;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $assessment = $this->assessmentService->getById($id);

        return $this->sendResponse(new AssessmentDetailResource($assessment), 'Assessment retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

    public function resultDetail(string $id, string $attemptId): JsonResponse
    {
        $response = $this->assessmentService->resultDetail($id, $attemptId);

        return $this->sendResponse($response);
    }
}
