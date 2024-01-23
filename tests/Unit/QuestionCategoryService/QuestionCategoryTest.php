<?php

namespace Tests\Unit\QuestionCategoryService;

use App\Services\Question\QuestionCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\BaseService\BaseServiceTest;

class QuestionCategoryTest extends BaseServiceTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(QuestionCategoryService::class);
    }
}
