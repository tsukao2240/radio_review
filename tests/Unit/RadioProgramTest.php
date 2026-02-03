<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RadioProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_radio_program_has_fillable_attributes()
    {
        $fillable = ['station_id', 'title', 'cast', 'start', 'end', 'info', 'url', 'image'];
        $program = new RadioProgram();

        $this->assertEquals($fillable, $program->getFillable());
    }

    public function test_radio_program_uses_correct_table()
    {
        $program = new RadioProgram();

        $this->assertEquals('radio_programs', $program->getTable());
    }

    public function test_radio_program_can_be_created()
    {
        $program = RadioProgram::create([
            'station_id' => 'TBS',
            'title' => 'Test Program',
            'cast' => 'Test Cast',
            'start' => '10:00',
            'end' => '11:00',
            'info' => 'Test Info',
            'url' => 'http://example.com',
            'image' => 'http://example.com/image.jpg'
        ]);

        $this->assertDatabaseHas('radio_programs', [
            'station_id' => 'TBS',
            'title' => 'Test Program'
        ]);
    }

    public function test_radio_program_can_be_updated()
    {
        $program = RadioProgram::factory()->create(['title' => 'Original Title']);

        $program->update(['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $program->fresh()->title);
    }

    public function test_radio_program_can_be_deleted()
    {
        $program = RadioProgram::factory()->create();
        $programId = $program->id;

        $program->delete();

        $this->assertDatabaseMissing('radio_programs', ['id' => $programId]);
    }

    public function test_radio_program_can_have_nullable_fields()
    {
        $program = RadioProgram::create([
            'station_id' => 'TBS',
            'title' => 'Test Program',
            'start' => '10:00',
            'end' => '11:00'
        ]);

        $this->assertNull($program->cast);
        $this->assertNull($program->info);
        $this->assertNull($program->url);
        $this->assertNull($program->image);
    }
}
