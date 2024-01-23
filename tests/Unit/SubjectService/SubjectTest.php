<?php

namespace Tests\Unit\SubjectService;

use App\Services\SubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\BaseService\BaseServiceTest;

class SubjectTest extends BaseServiceTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(SubjectService::class);
    }
}
