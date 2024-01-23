<?php

namespace Tests\Unit\UserService;

use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\BaseService\BaseServiceTest;

class UserTest extends BaseServiceTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(UserService::class);
    }
}
