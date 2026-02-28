<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPocket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserPocketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'initial_balance' => 'required|numeric|min:0'
        ]);

        $pocket = UserPocket::create([
            'user_id' => Auth::guard('api')->id(),
            'name' => $request->name,
            'balance' => $request->initial_balance
        ]);

        return response()->json([
            'status' => 200,
            'error' => false,
            'message' => 'Berhasil membuat pocket baru.',
            'data' => [
                'id' => $pocket->id
            ]
        ]);
    }
}
