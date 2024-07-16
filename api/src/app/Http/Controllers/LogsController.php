<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function follow(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $logs = $user->follow_logs;
        return response()->json($logs);
    }
}
