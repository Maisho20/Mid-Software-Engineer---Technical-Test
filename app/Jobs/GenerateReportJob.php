<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Report;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $reportId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $report = Report::find($this->reportId);
        if (!$report) return;

        $directory = 'reports';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $query = $report->type === 'INCOME'
            ? Income::where('pocket_id', $report->pocket_id)
            : Expense::where('pocket_id', $report->pocket_id);

        $data = $query
            ->whereDate('created_at', $report->date)
            ->get();

        $filename = "{$directory}/{$report->pocket_id}_{$report->type}_{$report->date}.xlsx";

        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        $content = "Amount,Notes,Date\n";

        foreach ($data as $row) {
            $content .= "{$row->amount},{$row->notes},{$row->created_at}\n";
        }

        Storage::disk('public')->put($filename, $content);

        $report->update([
            'file_path' => $filename,
            'status' => 'DONE'
        ]);
    }
}
