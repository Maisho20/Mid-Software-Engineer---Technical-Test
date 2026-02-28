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

        $query = $report->type === 'INCOME'
            ? Income::where('pocket_id', $report->pocket_id)
            : Expense::where('pocket_id', $report->pocket_id);

        $data = $query->whereDate('created_at', $report->date)->get();

        $filename = "reports/{$report->id}.xlsx";

        $content = "Amount,Notes,Date\n";

        foreach ($data as $row) {
            $content .= "{$row->amount},{$row->notes},{$row->created_at}\n";
        }

        Storage::put($filename, $content);

        $report->update([
            'file_path' => $filename,
            'status' => 'DONE'
        ]);
    }
}
