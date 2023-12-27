<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionCategoryRequest;
use App\Http\Requests\UpdateQuestionCategoryRequest;
use App\Http\Resources\QuestionCategoryResource;
use App\Models\QuestionCategory;
use App\Services\Question\QuestionCategoryService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QuestionCategoryController extends Controller
{
    protected QuestionCategoryService $questionCategoryService;

    public function __construct(QuestionCategoryService $questionCategoryService)
    {
        $this->questionCategoryService = $questionCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $passages = $this->questionCategoryService->getList(QuestionCategoryResource::class);

        return $this->sendResponse($passages, 'Categories retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionCategoryRequest $request): JsonResponse
    {
        $this->questionCategoryService->create($request->validated());

        return $this->sendResponse(
            null,
            'Category created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $passage = $this->questionCategoryService->getById($id);

        return $this->sendResponse(new QuestionCategory($passage), 'Category retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionCategoryRequest $request, string $id): JsonResponse
    {
        $this->questionCategoryService->update($id, $request->validated());

        return $this->sendResponse(
            null,
            'Category updated successfully',
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->questionCategoryService->delete($id);

        return $this->sendResponse(
            null,
            'Category deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
