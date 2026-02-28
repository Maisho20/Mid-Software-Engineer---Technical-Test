<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\UserPocket;
use App\Jobs\GenerateReportJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function download($id)
    {
        $userId = Auth::guard('api')->id();

        $report = Report::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        // dd($userId, $report, $id);

        if (!$report) {
            return response()->json([
                'status' => 404,
                'error' => true,
                'message' => 'Report tidak ditemukan.'
            ], 404);
        }

        if ($report->status !== 'DONE' || !$report->file_path) {
            return response()->json([
                'status' => 404,
                'error' => true,
                'message' => 'Report belum selesai dibuat.'
            ], 404);
        }

        if (!Storage::exists($report->file_path)) {
            return response()->json([
                'status' => 404,
                'error' => true,
                'message' => 'File report tidak ditemukan.'
            ], 404);
        }

        return Storage::download(
            $report->file_path,
            $report->id . '.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        );
    }
}
