<?php

namespace Tests\Unit;

use App\Models\Reader;
use App\Models\ScanSession;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_can_be_created_with_valid_data(): void {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas('tags', [
            'epc' => $tag->epc
        ]);

        $this->assertNotNull($tag->epc);
        $this->assertNotNull($tag->protocol);
    }

    public function test_reader_can_be_created_with_valid_data(): void {
        $reader = Reader::factory()->create();

        $this->assertDatabaseHas('readers', [
            'name' => $reader->name
        ]);

        $this->assertNotNull($reader->name);
    }

    public function test_scan_session_can_be_created(): void {
        $scanner = ScanSession::factory()->create();

        $this->assertDatabaseHas('scan_sessions', [
            'protocol' => $scanner->protocol
        ]);

        $this->assertNotNull($scanner->protocol);
    }
}