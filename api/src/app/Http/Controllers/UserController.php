<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFollowResource;
use App\Http\Resources\UserItemResource;
use App\Http\Resources\UserMailResource;
use App\Http\Resources\UserResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_level' => ['required', 'int', 'min:1'],
            'max_level' => ['required', 'int', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $users = User::where('level', '>', $request->min_level)->where("level", "<", $request->max_level)->get();
        return response()->json(UserResource::collection($users), 200);
    }

    public function show(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        return response()->json(UserResource::make($user));
    }

    // 所持アイテムリスト
    public function item(Request $request)
    {
        // JSON文字列にして返す
        $user = User::findOrFail($request->user_id);
        $items = $user->items;  // リレーション
        $response['items'] = UserItemResource::collection($items);
        return response()->json($response);
    }

    // 受信メールリスト
    public function mail(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $mails = $user->mails;
        return response()->json(UserMailResource::collection($mails));
    }

    // フォローリスト
    public function follow(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $follows = $user->follows;
        // 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($follows); $i++) {
            $following_user = Follow::where('user_id', '=', $follows[$i]['following_user_id'])
                ->where('following_user_id', '=', $follows[$i]['user_id'])->exists();
            $follows[$i]['is_agreement'] = $following_user === true ? 1 : 0;
        }
        return response()->json(UserFollowResource::collection($follows));
    }
}
