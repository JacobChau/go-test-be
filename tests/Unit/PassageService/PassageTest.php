<?php

namespace Tests\Unit\PassageService;

use App\Services\PassageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\BaseService\BaseServiceTest;

class PassageTest extends BaseServiceTest
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(PassageService::class);
    }
}
