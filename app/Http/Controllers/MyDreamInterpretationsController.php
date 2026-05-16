<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterpretationDiaryTransferRequest;
use App\Models\DreamInterpretation;
use App\Models\Report;
use App\Services\InterpretationDiaryTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyDreamInterpretationsController extends Controller
{
    public function index(): View
    {
        $interpretations = DreamInterpretation::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        /** @var \Illuminate\Pagination\LengthAwarePaginator<DreamInterpretation> $interpretations */
        $interpretations->load(['report:id,user_id']);

        return view('dream-interpretations.index', compact('interpretations'));
    }

    public function transferCreate(string $hash): View|RedirectResponse
    {
        $interpretation = DreamInterpretation::query()
            ->where('hash', $hash)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (($interpretation->processing_status ?? '') !== 'completed') {
            return redirect()
                ->route('dream-analyzer.show', $interpretation->hash)
                ->with('info', 'Перенести в дневник можно только завершённое толкование.');
        }

        $linkedReport = $this->resolveLinkedReport($interpretation);
        if ($linkedReport) {
            return redirect()
                ->route('reports.show', $linkedReport)
                ->with('info', 'Это толкование уже связано с записью в дневнике.');
        }

        return view('dream-interpretations.transfer', [
            'interpretation' => $interpretation,
            'defaultAccess' => 'none',
        ]);
    }

    public function transferStore(StoreInterpretationDiaryTransferRequest $request, string $hash, InterpretationDiaryTransferService $service): RedirectResponse
    {
        $interpretation = DreamInterpretation::query()
            ->where('hash', $hash)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (($interpretation->processing_status ?? '') !== 'completed') {
            return redirect()
                ->route('dream-analyzer.show', $interpretation->hash)
                ->with('error', 'Перенос возможен только для завершённого толкования.');
        }

        $linkedReport = $this->resolveLinkedReport($interpretation);
        if ($linkedReport) {
            return redirect()
                ->route('reports.show', $linkedReport)
                ->with('info', 'Это толкование уже в дневнике.');
        }

        try {
            $report = $service->transfer(
                $interpretation,
                $request->user(),
                $request->validated('report_date'),
                $request->validated('access_level'),
                $request->boolean('allow_public_linking')
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Толкование добавлено в дневник как опубликованный отчёт.');
    }

    private function resolveLinkedReport(DreamInterpretation $interpretation): ?Report
    {
        if (! $interpretation->report_id) {
            return null;
        }

        return Report::query()
            ->whereKey($interpretation->report_id)
            ->where('user_id', auth()->id())
            ->first();
    }
}
