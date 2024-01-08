<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Resources\GroupResource;
use App\Services\GroupService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    protected UserService $userService;
    protected GroupService $groupService;

    public function __construct(UserService $userService, GroupService $groupService) {
        $this->userService = $userService;
        $this->groupService = $groupService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $relations = [];
        if (request()->has('include')) {
            $relations = explode(',', request()->get('include'));
        }

        $groups = $this->groupService->getList(GroupResource::class, request()->all(), null, $relations);

        return $this->sendResponse($groups, 'Groups retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request) : JsonResponse
    {
        $this->groupService->create($request->validated());

        return $this->sendResponse(
            null,
            'Group created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $group = $this->groupService->getById($id, ['createdBy']);

        return $this->sendResponse(new GroupResource($group), 'Group retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
}
