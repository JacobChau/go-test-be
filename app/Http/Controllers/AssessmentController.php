<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Resources\AssessmentDetailResource;
use App\Http\Resources\AssessmentResource;
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
        $relations = ['questions', 'groups'];
        $assessment = $this->assessmentService->getById($id, $relations);

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
}
