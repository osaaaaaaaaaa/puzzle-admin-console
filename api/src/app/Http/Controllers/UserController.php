<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserFollowResource;
use App\Http\Resources\UserItemResource;
use App\Http\Resources\UserMailResource;
use App\Http\Resources\UserResource;
use App\Models\Achievement;
use App\Models\Attached_Item;
use App\Models\FollowingUser;
use App\Models\FollowLogs;
use App\Models\ItemLogs;
use App\Models\Level;
use App\Models\MailLogs;
use App\Models\StageResult;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ErrorHandler\Debug;

class UserController extends Controller
{
    // ユーザー情報取得
    public function show(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 存在チェック
        $user = User::findOrFail($request->user_id);

        // 設定しているアチーブメントの称号を取得する
        $title = '';
        if ($user->achievement_id > 0) {
            $achievement = $user->achievements()->selectRaw('title')->first();
            if (!empty($achievement->title)) {
                $title = $achievement->title;
            }
        }

        // 返す値をまとめる
        $response = [
            'name' => $user->name,
            'achievement_id' => $user->achievement_id,
            'title' => $title,
            'stage_id' => $user->stage_id,
            'icon_id' => $user->icon_id
        ];

        return response()->json($response);
    }

    // ユーザー情報登録
    public function store(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // トランザクション処理
            $user = DB::transaction(function () use ($request) {
                // 登録処理
                $user = User::create([
                    'name' => $request->name,
                    'achievement_id' => 0,
                    'stage_id' => 1,
                    'icon_id' => 1
                ]);

                return $user;
            });
            return response()->json(['user_id' => $user->id]);
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
            'name' => ['required', 'string'],
            'achievement_id' => ['required', 'integer'],
            'stage_id' => ['required', 'integer'],
            'icon_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 存在チェック
        $user = User::findOrFail($request->user_id);
        $achievement = Achievement::where('id', '=', $request->achievement_id)->first();
        if ($request->achievement_id != 0 && empty($achievement)) {
            // ID指定が0以外 && 存在しない場合
            return response()->json(['error' => 'Achievement not found'], 404);
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $user) {
                $user->name = $request->name;
                $user->achievement_id = $request->achievement_id;
                $user->stage_id = $request->stage_id > $user->stage_id ? $request->stage_id : $user->stage_id;  // 値が高い方に更新する
                $user->icon_id = $request->icon_id;
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

    // ステージリザルト更新
    public function updateStageResult(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'required'],       // ユーザーID
            'stage_id' => ['int', 'required'],      // ステージID
            'score' => ['int', 'required']          // 更新する値
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        User::findOrFail($request->user_id);

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {

                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $stage_result = StageResult::firstOrCreate(
                    ['user_id' => $request->user_id, 'stage_id' => $request->stage_id],    // 検索する条件値
                    ['score' => $request->score]   // 生成するときに代入するカラム
                );

                // 今回のスコアが高い場合は更新する
                if ($stage_result->score < $request->score) {
                    $stage_result->score = $request->score;
                }

                $stage_result->save();
            });

            return response()->json();

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ランキング取得
    public function showRanking(Request $request)
    {
        User::findOrFail($request->user_id);

        // ユーザーのリザルトを取得する
        $userResult = StageResult::selectRaw("user_id,name,achievement_id,SUM(score) AS total")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->where('users.id', '=', $request->user_id)
            ->groupBy('stage_results.user_id')
            ->first();

        // ランキング上位の情報を取得する
        $results = StageResult::selectRaw("user_id,name,achievement_id,SUM(score) AS total")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->groupBy("stage_results.user_id")
            ->orderBy('total', 'desc')
            ->limit(100)
            ->get();

        // 返すデータをまとめる
        $response = [
            "results" => $results,
            "mydata" => $userResult,
        ];

        return response()->json($response);
    }

    // フォロー内でのランキング
    public function showFollowRanking(Request $request)
    {
        User::findOrFail($request->user_id);

        // ユーザーのリザルトを取得する
        $userResult = StageResult::selectRaw("user_id,name,achievement_id,SUM(score) AS total")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->where('users.id', '=', $request->user_id)
            ->groupBy('stage_results.user_id')
            ->first()
            ->attributesToArray();

        // フォロー内でランキング上位の情報を取得する
        $results = StageResult::selectRaw("stage_results.user_id,name,achievement_id,SUM(score) AS total")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->join('following_users AS fu', 'users.id', '=', 'fu.following_user_id')
            ->where('fu.user_id', '=', $request->user_id)
            ->groupBy("stage_results.user_id")
            ->orderBy('total', 'desc')
            ->limit(100)
            ->get()
            ->toArray();    // 取得するときに配列に変換する

        // 返すデータをまとめる
        $results[] = $userResult;

        // totalを基準に降順に並び替える
        $totalArray = array_column($results, 'total');
        array_multisort($totalArray, SORT_DESC, $results);

        return response()->json($results);
    }
}
