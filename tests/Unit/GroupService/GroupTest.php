<?php

namespace Tests\Unit\GroupService;

use App\Services\GroupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\BaseService\BaseServiceTest;

class GroupTest extends BaseServiceTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(GroupService::class);
    }
}
