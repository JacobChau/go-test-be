<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePassageRequest;
use App\Http\Requests\UpdatePassageRequest;
use App\Http\Resources\PassageDetailResource;
use App\Http\Resources\PassageResource;
use App\Models\Passage;
use App\Services\PassageService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PassageController extends Controller
{
    protected PassageService $passageService;

    public function __construct(PassageService $passageService)
    {
        $this->passageService = $passageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $passages = $this->passageService->getList(PassageResource::class, request()->all());

        return $this->sendResponse($passages, 'Passages retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePassageRequest $request): JsonResponse
    {
        $this->passageService->create($request->validated());

        return $this->sendResponse(
            null,
            'Passage created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $passage = $this->passageService->getById($id);

        return $this->sendResponse(new PassageDetailResource($passage), 'Passage retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePassageRequest $request, Passage $passage): JsonResponse
    {
        $this->passageService->update($passage->id, $request->validated());

        return $this->sendResponse(
            null,
            'Passage updated successfully',
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->passageService->delete($id);

        return $this->sendResponse(
            null,
            'Passage deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
