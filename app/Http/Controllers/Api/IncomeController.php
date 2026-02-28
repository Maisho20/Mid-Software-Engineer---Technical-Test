<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\UserPocket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pocket_id' => 'required|uuid',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string'
        ]);

        $userId = Auth::guard('api')->id();

        $result = DB::transaction(function () use ($request, $userId) {

            $pocket = UserPocket::where('id', $request->pocket_id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $income = Income::create([
                'user_id' => $userId,
                'pocket_id' => $pocket->id,
                'amount' => $request->amount,
                'notes' => $request->notes
            ]);

            $pocket->increment('balance', $request->amount);

            return [
                'income' => $income,
                'pocket' => $pocket->fresh()
            ];
        });

        return response()->json([
            'status' => 200,
            'error' => false,
            'message' => 'Berhasil menambahkan income.',
            'data' => [
                'id' => $result['income']->id,
                'pocket_id' => $result['income']->pocket_id,
                'current_balance' => $result['pocket']->balance
            ]
        ]);
    }
}
