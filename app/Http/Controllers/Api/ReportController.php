<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\UserPocket;
use App\Jobs\GenerateReportJob;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function createReport(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:INCOME,EXPENSE',
            'date' => 'required|date_format:Y-m-d'
        ]);

        $userId = Auth::guard('api')->id();

        $pocket = UserPocket::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // dd($pocket);

        $report = Report::create([
            'user_id' => $userId,
            'pocket_id' => $pocket->id,
            'type' => $request->type,
            'date' => $request->date,
            'status' => 'PENDING'
        ]);

        GenerateReportJob::dispatch($report->id);

        return response()->json([
            'status' => 200,
            'error' => false,
            'message' => 'Report sedang dibuat. Silahkan check berkala pada link berikut.',
            'data' => [
                'link' => url("reports/{$report->id}")
            ]
        ]);
    }
}
