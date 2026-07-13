<?php

namespace Tests\Feature;

// use App\Models\Tag;
// use App\Models\ScanSession;
// use App\Models\Reader;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    public function test_the_feature_test_class_is_discoverable(): void
    {
        $this->assertInstanceOf(TestCase::class, $this);
    }
}
