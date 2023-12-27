<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Services\SubjectService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SubjectController extends Controller
{
    protected SubjectService $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $subjects = $this->subjectService->getList(SubjectResource::class);

        return $this->sendResponse($subjects, 'Subjects retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $this->subjectService->create($request->validated());

        return $this->sendResponse(
            null,
            'Subject created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $subject = $this->subjectService->getById($id);

        return $this->sendResponse(new SubjectResource($subject), 'Subject retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubjectRequest $request, string $id): JsonResponse
    {
        $this->subjectService->update($id, $request->validated());

        return $this->sendResponse(
            null,
            'Subject updated successfully',
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->subjectService->delete($id);

        return $this->sendResponse(
            null,
            'Subject deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
