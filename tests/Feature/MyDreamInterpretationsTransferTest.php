<?php

namespace Tests\Feature;

use App\Models\Dream;
use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationResult;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MyDreamInterpretationsTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_dream_interpretations_index(): void
    {
        $this->get(route('dream-interpretations.index'))
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_completed_interpretation_can_be_transferred_to_published_report(): void
    {
        $user = User::factory()->create([
            'nickname' => 'dreamuser'.Str::lower(Str::random(6)),
        ]);
        $hash = Str::random(32);

        DreamInterpretation::create([
            'hash' => $hash,
            'user_id' => $user->id,
            'dream_description' => 'Сон про полёт над городом.',
            'processing_status' => 'completed',
            'traditions' => ['eclectic'],
            'analysis_type' => 'single',
        ]);

        $this->actingAs($user)->post(route('dream-interpretations.transfer.store', $hash), [
            'report_date' => '2020-01-15',
            'access_level' => 'none',
            'allow_public_linking' => '0',
        ]);

        $interpretation = DreamInterpretation::where('hash', $hash)->firstOrFail();
        /** @var Report|null $report */
        $report = Report::find($interpretation->report_id);

        $this->assertNotNull($report);
        $this->assertSame((string) $user->id, (string) $report->user_id);
        $this->assertSame('published', $report->status);
        $this->assertSame('none', $report->access_level);
        $this->assertSame($interpretation->id, $report->analysis_id);
        $this->assertFalse((bool) $interpretation->allow_public_linking);
    }

    public function test_transfer_strips_html_from_dream_title(): void
    {
        $user = User::factory()->create([
            'nickname' => 'dreamuser'.Str::lower(Str::random(6)),
        ]);
        $hash = Str::random(32);

        $interpretation = DreamInterpretation::create([
            'hash' => $hash,
            'user_id' => $user->id,
            'dream_description' => 'Сон про воду.',
            'processing_status' => 'completed',
            'traditions' => ['eclectic'],
            'analysis_type' => 'single',
        ]);

        DreamInterpretationResult::create([
            'dream_interpretation_id' => $interpretation->id,
            'type' => 'single',
            'dream_title' => '<h2>Пища силы и тень ревности</h2>',
            'dream_detailed' => 'Текст анализа сна.',
            'dream_type' => 'Яркий сон',
        ]);

        $this->actingAs($user)->post(route('dream-interpretations.transfer.store', $hash), [
            'report_date' => '2020-01-15',
            'access_level' => 'none',
            'allow_public_linking' => '0',
        ])->assertRedirect();

        $dream = Dream::query()->where('report_id', $interpretation->fresh()->report_id)->first();

        $this->assertNotNull($dream);
        $this->assertSame('Пища силы и тень ревности', $dream->title);
        $this->assertSame('Сон про воду.', $dream->description);
        $this->assertStringNotContainsString('Текст анализа', (string) $dream->description);
    }

    public function test_transfer_uses_user_dream_text_not_api_analysis(): void
    {
        $user = User::factory()->create([
            'nickname' => 'dreamuser'.Str::lower(Str::random(6)),
        ]);
        $hash = Str::random(32);

        $interpretation = DreamInterpretation::create([
            'hash' => $hash,
            'user_id' => $user->id,
            'dream_description' => 'Я шла по коридору школы и искала кабинет химии.',
            'processing_status' => 'completed',
            'traditions' => ['eclectic'],
            'analysis_type' => 'single',
        ]);

        DreamInterpretationResult::create([
            'dream_interpretation_id' => $interpretation->id,
            'type' => 'single',
            'dream_title' => 'Коридор выбора',
            'dream_detailed' => '<p>Сон символизирует внутренний поиск и незавершённость.</p>',
            'dream_type' => 'Яркий сон',
        ]);

        $this->actingAs($user)->post(route('dream-interpretations.transfer.store', $hash), [
            'report_date' => '2020-01-15',
            'access_level' => 'none',
            'allow_public_linking' => '0',
        ])->assertRedirect();

        $dream = Dream::query()->where('report_id', $interpretation->fresh()->report_id)->firstOrFail();

        $this->assertStringContainsString('коридору школы', (string) $dream->description);
        $this->assertStringNotContainsString('внутренний поиск', (string) $dream->description);
    }
}
