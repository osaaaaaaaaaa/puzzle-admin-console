<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConstantResource;
use App\Models\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConstantController extends Controller
{
    // 定数取得
    public function show(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'type' => ['int', 'min:1'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $constant = Constant::where('type', $request->type)->firstOrFail();

        return response()->json(ConstantResource::make($constant));
    }
}
