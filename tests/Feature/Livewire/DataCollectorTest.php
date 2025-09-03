<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DataCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class DataCollectorTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(DataCollector::class)
            ->assertStatus(200);
    }
}
