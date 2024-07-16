<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFollowResource;
use App\Http\Resources\UserItemResource;
use App\Http\Resources\UserMailResource;
use App\Http\Resources\UserResource;
use App\Models\Attached_Item;
use App\Models\FollowingUser;
use App\Models\FollowLogs;
use App\Models\ItemLogs;
use App\Models\MailLogs;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // レベル指定でユーザー情報取得
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

    // ユーザー情報取得
    public function show(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        return response()->json(UserResource::make($user));
    }

    // ユーザー情報登録
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

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 登録処理
                User::create([
                    'name' => $request->name,
                    'level' => $request->level,
                    'exp' => $request->exp,
                    'life' => $request->life,
                ]);
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ユーザー情報更新
    public function update(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1'],
            'name' => ['string'],
            "level" => ['int', 'min:1'],
            "exp" => ['int', 'min:0'],
            "life" => ['int', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 更新処理
        $user = User::findOrFail($request->user_id);

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $user) {
                $user->name = $request->name;
                $user->level = $request->level;
                $user->exp = $request->exp;
                $user->life = $request->life;
                $user->save();
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 所持アイテムリスト取得
    public function showItem(Request $request)
    {
        // JSON文字列にして返す
        $user = User::findOrFail($request->user_id);
        $items = $user->items;  // リレーション
        $response['items'] = UserItemResource::collection($items);
        return response()->json($response);
    }

    // 所持アイテム更新
    public function updateItem(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'int', 'min:1'],
            'item_id' => ['required', 'int', 'min:1'],
            'option_id' => ['required', 'int', 'min:1'],
            'allie_amount' => ['required', 'int'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 指定したユーザーが存在するかどうか
        User::findOrFail($request->user_id);

        // レコード存在チェック
        $userItem = UserItem::where('user_id', '=', $request->user_id)->where("item_id", "=",
            $request->item_id)->first();

        //----------------------------------
        // 登録処理(所持していないアイテムの入手)
        //----------------------------------
        // レコードが存在しなかった&&加減する値が0以上の場合[登録可能]
        if (empty($userItem) && $request->allie_amount > 0) {
            try {
                // トランザクション処理
                DB::transaction(function () use ($request) {
                    // 登録処理
                    UserItem::create([
                        'user_id' => $request->user_id,
                        'item_id' => $request->item_id,
                        'amount' => $request->allie_amount,
                    ]);

                    // ログテーブル登録処理
                    ItemLogs::create([
                        'user_id' => $request->user_id,
                        'item_id' => $request->item_id,
                        'option_id' => $request->option_id,
                        'allie_count' => $request->allie_amount
                    ]);
                });
                return response()->json();
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } // レコードが存在しなかった&&加減する値が0以下の場合[登録不可]
        elseif (empty($userItem) && $request->allie_amount <= 0) {
            abort(404);
        }

        //-------------------
        // 更新処理(レコードが存在する場合)
        //-------------------
        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $userItem) {
                // 加減算
                $userItem->amount += $request->allie_amount;

                // 個数が0未満になる場合
                if ($userItem->amount < 0) {
                    abort(400);
                }

                $userItem->save();

                // ログテーブル登録処理
                ItemLogs::create([
                    'user_id' => $request->user_id,
                    'item_id' => $request->item_id,
                    'option_id' => $request->option_id,
                    'allie_count' => $request->allie_amount
                ]);
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // フォローリスト取得
    public function showFollow(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        // リレーション
        $following_users = $user->follows;

        // フォローしているユーザーが存在しない場合
        if (empty($following_users)) {
            return response()->json(UserFollowResource::collection($following_users));
        }

        // 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($following_users); $i++) {
            $isFollow = FollowingUser::where('user_id', '=', $following_users[$i]->id)
                ->where('following_user_id', '=', $user->id)->exists();
            $following_users[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json(UserFollowResource::collection($following_users));
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

        // フォロー対象のユーザーが存在するかどうか
        User::findOrFail($request->user_id);

        // フォロー済みかどうか
        $frag = FollowingUser::where('user_id', '=', $request->user_id)->where("following_user_id", "=",
            $request->following_user_id)->exists();
        if ($frag) {
            abort(400);
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 登録処理
                FollowingUser::create([
                    'user_id' => $request->user_id,
                    'following_user_id' => $request->following_user_id,
                ]);

                // ログテーブル登録処理
                FollowLogs::create([
                    'user_id' => $request->user_id,
                    'target_user_id' => $request->following_user_id,
                    'action' => 1
                ]);
            });

            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

        // 対象のユーザーが存在するかどうか
        User::findOrFail($request->user_id);

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 削除処理
                FollowingUser::where('user_id', '=', $request->user_id)->where('following_user_id', '=',
                    $request->following_user_id)->delete();

                // ログテーブル登録処理
                FollowLogs::create([
                    'user_id' => $request->user_id,
                    'target_user_id' => $request->following_user_id,
                    'action' => 0
                ]);
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 受信メールリスト取得
    public function showMail(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $mails = $user->mails;

        foreach ($mails as $mail) {
            $frag = MailLogs::where('user_id', '=', $request->user_id)->where("mail_id", "=", $mail->mail_id)->exists();
            // ログに未登録の受信メールの場合
            if (!$frag) {
                // ログテーブル登録処理
                MailLogs::create([
                    'user_id' => $request->user_id,
                    'mail_id' => $mail->mail_id,
                    'action' => 0
                ]);
            }
        }

        return response()->json(UserMailResource::collection($mails));
    }

    // 受信メール開封
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

        // 指定したユーザーが存在するかどうか
        User::findOrFail($request->user_id);

        // レコード存在チェック・受け取り済みかどうかチェック
        $userMail = UserMail::where('user_id', '=', $request->user_id)->where("mail_id", "=",
            $request->mail_id)->get()->first();
        if (empty($userMail)) {
            abort(404);
        } elseif ($userMail->is_received === 1) {
            abort(404);
        }

        //------------------------
        // 添付アイテムの受け取り処理
        //------------------------
        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $userMail) {

                // メールの添付アイテムを取得
                $attachedItems = Attached_Item::where('mail_id', '=', $request->mail_id)->get();
                if (!empty($attachedItems)) {
                    foreach ($attachedItems as $item) {

                        // 今回受け取るアイテムに関するレコードが、所持アイテムテーブルに存在するかチェック
                        $userItem = UserItem::where('user_id', '=', $request->user_id)->where('item_id', '=',
                            $item->item_id)->get()->first();

                        // アイテムを所持していない場合は登録する
                        if (empty($userItem)) {
                            UserItem::create([
                                'user_id' => $request->user_id,
                                'item_id' => $item->item_id,
                                'amount' => $item->amount,
                            ]);
                        } // 所持アイテムを更新する
                        else {
                            $userItem->amount += $item->amount;
                            $userItem->save();
                        }
                    }
                }

                // 受信メールを開封済みにする
                $userMail->is_received = 1;
                $userMail->save();

                // ログテーブル登録処理
                MailLogs::create([
                    'user_id' => $request->user_id,
                    'mail_id' => $request->mail_id,
                    'action' => 1
                ]);

            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
