<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\UserPocket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExpenseController extends Controller
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

            if ($pocket->balance < $request->amount) {
                abort(response()->json([
                    'status' => 400,
                    'error' => true,
                    'message' => 'Saldo tidak mencukupi.'
                ], 400));
            }

            $expense = Expense::create([
                'user_id' => $userId,
                'pocket_id' => $pocket->id,
                'amount' => $request->amount,
                'notes' => $request->notes
            ]);

            $pocket->decrement('balance', $request->amount);

            return [
                'expense' => $expense,
                'pocket' => $pocket->fresh()
            ];
        });

        return response()->json([
            'status' => 200,
            'error' => false,
            'message' => 'Berhasil menambahkan expense.',
            'data' => [
                'id' => $result['expense']->id,
                'pocket_id' => $result['expense']->pocket_id,
                'current_balance' => $result['pocket']->balance
            ]
        ]);
    }
}
