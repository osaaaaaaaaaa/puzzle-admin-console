<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFollowResource;
use App\Http\Resources\UserItemResource;
use App\Http\Resources\UserMailResource;
use App\Http\Resources\UserResource;
use App\Models\Attached_Item;
use App\Models\Follow;
use App\Models\FollowingUser;
use App\Models\Inventory_Item;
use App\Models\Item;
use App\Models\Received_Mail;
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
    public function showItem(Request $request)
    {
        // JSON文字列にして返す
        $user = User::findOrFail($request->user_id);
        $items = $user->items;  // リレーション
        $response['items'] = UserItemResource::collection($items);
        return response()->json($response);
    }

    // 受信メールリスト
    public function showMail(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $mails = $user->mails;
        return response()->json(UserMailResource::collection($mails));
    }

    // フォローリスト
    public function showFollow(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        // リレーション
        $following_users = $user->follows;

        if (empty($following_users)) {
            abort(404);
        }

        // 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($following_users); $i++) {
            $isFollow = FollowingUser::where('user_id', '=', $following_users[$i]->id)
                ->where('following_user_id', '=', $user->id)->exists();
            $following_users[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json(UserFollowResource::collection($following_users));
    }

    // 登録処理
    public function store(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'level' => ['required', 'int', 'min:1'],
            'exp' => ['required', 'int', 'min:0'],
            'life' => ['required', 'int', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'level' => $request->level,
            'exp' => $request->exp,
            'life' => $request->life,
        ]);

        return response()->json(['user_id' => $user->id]);
    }

    // 更新処理
    public function update(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int', 'min:1'],
            'name' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 更新処理
        $user = User::findOrFail($request->user_id);
        $user->name = $request->name;
        $user->save();

        return response()->json();  // []で返す
    }

    // フォロー登録
    public function storeFollow(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int'],
            'following_user_id' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // レコード存在チェック
        $frag = FollowingUser::where('user_id', '=', $request->user_id)->where("following_user_id", "=",
            $request->following_user_id)->exists();
        if ($frag) {
            abort(404);
        }

        // 登録処理
        $followingData = FollowingUser::create([
            'user_id' => $request->user_id,
            'following_user_id' => $request->following_user_id,
        ]);
        return response()->json(['id' => $followingData->id]);
    }

    // フォロー解除
    public function destroyFollow(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int'],
            'following_user_id' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 削除処理
        FollowingUser::where('user_id', '=', $request->user_id)->where('following_user_id', '=',
            $request->following_user_id)->delete();

        return response()->json();  // []で返す
    }

    // 所持アイテム更新処理
    public function updateItem(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int', 'min:1'],
            'item_id' => ['required', 'int', 'min:1'],
            'allie_amount' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // レコード存在チェック
        $userItem = Inventory_Item::where('user_id', '=', $request->user_id)->where("item_id", "=",
            $request->item_id)->first();

        //-------------------
        // 登録処理
        //-------------------
        // レコードが存在しなかった&&加減する値が0以上の場合
        if (empty($userItem) && $request->allie_amount > 0) {
            $userItem = Inventory_Item::create([
                'user_id' => $request->user_id,
                'item_id' => $request->item_id,
                'amount' => $request->allie_amount,
            ]);
            return response()->json(['id' => $userItem->id]);
        } elseif (empty($userItem) && $request->allie_amount <= 0) {
            abort(404);
        }

        //-------------------
        // 更新処理(加減処理)
        //-------------------
        // 加減した結果、0以上の場合
        if ($userItem->amount + $request->allie_amount >= 0) {
            $userItem->amount += $request->allie_amount;
        } else {
            $userItem->amount = 0;
        }
        $userItem->save();

        return response()->json();  // []で返す
    }

    // メール開封(更新)
    public function updateMail(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int'],
            'mail_id' => ['required', 'int']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // レコード存在チェック・受け取り済みかどうかチェック
        $userMail = Received_Mail::where('user_id', '=', $request->user_id)->where("mail_id", "=",
            $request->mail_id)->get()->first();
        if (empty($userMail)) {
            abort(404);
        } elseif ($userMail->is_received === 1) {
            abort(404);
        }

        //------------------------
        // 添付アイテムの受け取り処理
        //------------------------
        $attachedItems = Attached_Item::where('mail_id', '=', $request->mail_id)->get();
        if (!empty($attachedItems)) {
            foreach ($attachedItems as $item) {
                $userItem = Inventory_Item::where('user_id', '=', $request->user_id)->where('item_id', '=',
                    $item->item_id)->get()->first();
                // レコードが存在しない場合は登録する
                if (empty($userItem)) {
                    $userItem = Inventory_Item::create([
                        'user_id' => $request->user_id,
                        'item_id' => $item->item_id,
                        'amount' => $item->amount,
                    ]);
                } // レコードが存在する場合は更新(加算)する
                else {
                    $userItem->amount += $item->amount;
                    $userItem->save();
                }
            }
        }

        // 受け取り済みにする
        $userMail->is_received = 1;
        $userMail->save();

        return response()->json();  // []で返す
    }
}
