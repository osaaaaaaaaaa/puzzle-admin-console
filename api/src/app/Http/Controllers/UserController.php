<?php

namespace App\Http\Controllers;

use App\Http\Resources\StageResultResource;
use App\Http\Resources\UserFollowResource;
use App\Http\Resources\UserItemResource;
use App\Http\Resources\UserMailResource;
use App\Http\Resources\UserRecommendedResource;
use App\Http\Resources\UserResource;
use App\Models\Achievement;
use App\Models\Attached_Item;
use App\Models\FollowingUser;
use App\Models\FollowLogs;
use App\Models\Item;
use App\Models\ItemLogs;
use App\Models\Level;
use App\Models\MailLogs;
use App\Models\NGWord;
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
    const FOLLOW_LIMIT_MAX = 30;
    const STAGE_LIMIT_MAX = 22;

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

        // 合計スコアを取得する
        $total_score = $user->totalscore()->first() == null ? 0 : $user->totalscore()->pluck('total_score')->first();

        // 返す値をまとめる
        $response = [
            'name' => $user->name,
            'achievement_id' => $user->achievement_id,
            'title' => $title,
            'stage_id' => $user->stage_id,
            'icon_id' => $user->icon_id,
            'score' => $total_score,
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

                // 初期アイテム取得
                $items = Item::where('id', '=', 1)->orWhere('id', '=', 3)->get();

                foreach ($items as $item) {
                    // 所持アイテムに追加
                    UserItem::create([
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'amount' => 1,
                    ]);

                    // アイテムログテーブル登録処理
                    ItemLogs::create([
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'option_id' => 1,
                        'allie_count' => 1
                    ]);
                }

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
            return response()->json(['error' => "アチーブメントが存在しません"], 400);
        }

        // NGワードチェック
        $replaceName = str_replace(["　", " "], "", $request->name);  // 全角・半角スペース削除
        $ngWords = NGWord::pluck('word')->toArray();
        foreach ($ngWords as $ngWord) {
            if (stripos($replaceName, $ngWord) !== false) {
                // NGワードが含まれている場合の処理
                return response()->json(['error' => "使用できないワードが含まれています：" . $ngWord], 400);
            }
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
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1'],
            'type' => ['int'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 存在チェック
        $user = User::findOrFail($request->user_id);

        // タイプ指定がない場合は全て取得する
        if (empty($request->type)) {
            $items = $user->items;
        } else {
            $items = $user->items->where('type', '=', $request->type);
        }

        return response()->json(UserItemResource::collection($items));
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

        // 設定しているアチーブの称号, 合計スコア, 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($following_users); $i++) {

            // アチーブメントの称号取得処理
            $title = '';
            if ($following_users[$i]->achievement_id > 0) {
                $achievement = Achievement::selectRaw('title')
                    ->where('id', '=', $following_users[$i]->achievement_id)
                    ->first();
                if (!empty($achievement->title)) {
                    $title = $achievement->title;
                }
            }
            $following_users[$i]['title'] = $title;

            // 合計スコアを取得する
            $following_users[$i]['score'] = StageResult::selectRaw('SUM(score) AS total_score')
                ->where('stage_results.user_id', '=', $following_users[$i]->id)->first();
            $following_users[$i]['score'] = empty($following_users[$i]['score']['total_score']) ? 0 : $following_users[$i]['score']->total_score;

            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $following_users[$i]->id)
                ->where('following_user_id', '=', $user->id)->exists();
            $following_users[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json(UserFollowResource::collection($following_users));
    }

    // おすすめのユーザー取得
    public function showRecommendedUser(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        // 自分がフォローを返していないユーザーを取得する
        $followers = [];
        $tmp_followers = User::whereIn('id', function ($query) use ($user) {
            $query->select('user_id')
                ->from('following_users')
                ->inRandomOrder()
                ->where('following_user_id', '=', $user->id);
        })->get();
        foreach ($tmp_followers as $follower) {
            $isAgreement = FollowingUser::where('user_id', '=', $user->id)
                ->where('following_user_id', '=', $follower->id)->exists();

            if (!$isAgreement) {
                // 相互フォローではないユーザー情報を格納する
                $follower->is_follower = 1;
                $followers[] = $follower;
            }
        }

        // 自分がフォローしているユーザー・フォロワーを除外して検索する[ステージIDが近い || 始めた時期が近いプレイヤー]
        $recommended_users = User::
        where('id', '!=', $user->id)
            ->whereNotIn('id', $tmp_followers->pluck('id'))
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('following_user_id')
                    ->from('following_users')
                    ->where('user_id', '=', $user->id);
            })
            ->where(function ($query) use ($user) {
                $query->whereBetween('stage_id', [$user->stage_id - 10, $user->stage_id + 10])
                    ->orWhereBetween('created_at', [$user->created_at->subDays(14), $user->created_at->addDays(14)]);
            })
            ->inRandomOrder()
            ->limit(30)
            ->get()
            ->toArray();

        // 配列を結合してユーザーの情報をまとめる
        $users = array_merge($followers, $recommended_users);

        // 指定した件目以降のデータを消す
        if (count($users) > self::FOLLOW_LIMIT_MAX) {
            array_splice($users, self::FOLLOW_LIMIT_MAX, count($users) - self::FOLLOW_LIMIT_MAX);
        }

        // 設定しているアチーブの称号, 合計スコア, 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($users); $i++) {

            // アチーブメントの称号取得処理
            $title = '';
            if ($users[$i]['achievement_id'] > 0) {
                $achievement = Achievement::selectRaw('title')
                    ->where('id', '=', $users[$i]['achievement_id'])
                    ->first();
                if (!empty($achievement->title)) {
                    $title = $achievement->title;
                }
            }
            $users[$i]['title'] = $title;

            // 合計スコアを取得する
            $users[$i]['score'] = StageResult::selectRaw('SUM(score) AS total_score')
                ->where('stage_results.user_id', '=', $users[$i]['id'])->first();
            $users[$i]['score'] = empty($users[$i]['score']['total_score']) ? 0 : $users[$i]['score']['total_score'];

            // フォロワーかどうか
            $users[$i]['is_follower'] = !empty($users[$i]['is_follower']) === true ? 1 : 0;
        }

        return response()->json(UserRecommendedResource::collection($users));
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

        // ユーザーが存在するかどうか
        $user = User::findOrFail($request->user_id);
        User::findOrFail($request->following_user_id);

        // フォローしている人数が上限に達していないかチェック
        $follow_cnt = FollowingUser::selectRaw('COUNT(*) AS total_following')
            ->where('user_id', '=', $user->id)->first();
        if ($follow_cnt['total_following'] >= self::FOLLOW_LIMIT_MAX) {
            return response()->json(['error' => "フォローした人数が上限に達しています"], 400);
        }

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

    // ステージリザルト取得
    public function showStageResult(Request $request)
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

        // ステージリザルト取得のリレーション
        $result = $user->stageResult()->get();
        if (empty($result)) {
            return response()->json(['error' => "リザルトがない"], 400);
        }

        return response()->json(StageResultResource::collection($result));
    }

    // ステージクリア処理
    public function updateStageClear(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'required'],       // ユーザーID
            'stage_id' => ['int', 'required'],      // ステージID
            'is_medal1' => ['boolean', 'required'], // メダル１を取得したかどうか
            'is_medal2' => ['boolean', 'required'], // メダル２を取得したかどうか
            'time' => ['numeric', 'required'],       // 時間
            'score' => ['int', 'required']          // 更新する値
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        $user = User::findOrFail($request->user_id);

        try {
            // トランザクション処理
            $response = DB::transaction(function () use ($request, $user) {

                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $stage_result = StageResult::firstOrCreate(
                    ['user_id' => $request->user_id, 'stage_id' => $request->stage_id],    // 検索する条件値
                    [
                        'is_medal1' => $request->is_medal1,
                        'is_medal2' => $request->is_medal2,
                        'time' => $request->time,
                        'score' => $request->score
                    ]   // 生成するときに代入するカラム
                );

                // メダルを初獲得したかどうかチェック
                $stage_result->is_medal1 = !$stage_result->is_medal1 && $request->is_medal1 ? 1 : $stage_result->is_medal1;
                $stage_result->is_medal2 = !$stage_result->is_medal2 && $request->is_medal2 ? 1 : $stage_result->is_medal2;
                // 今回のスコアが高い場合は更新する
                if ($request->score > $stage_result->score) {
                    $stage_result->score = $request->score;
                    $stage_result->time = $request->time;
                }
                $stage_result->save();

                // ステージが初クリア&&ステージ上限値以下の場合はユーザーのステージIDを加算する
                $user->stage_id += $request->stage_id == $user->stage_id && $user->stage_id < self::STAGE_LIMIT_MAX ? 1 : 0;
                $user->save();

                return StageResultResource::make($stage_result);
            });

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ランキング取得
    public function showRanking(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        // ランキング上位の情報を取得する
        $results = StageResult::selectRaw("users.id AS user_id,name,IFNULL(title,'') AS title,users.stage_id AS stage_id,icon_id,SUM(score) AS score")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->leftJoin('achievements', 'achievements.id', '=', 'users.achievement_id')
            ->groupBy("stage_results.user_id")
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()->toArray();

        // 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($results); $i++) {
            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $results[$i]['user_id'])
                ->where('following_user_id', '=', $user->id)->exists();
            $results[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json($results);
    }

    // フォロー内でのランキング
    public function showFollowRanking(Request $request)
    {
        $user = User::findOrFail($request->user_id);


        // ユーザーのリザルトを取得する
        $userFrag = StageResult::where('user_id', '=', $request->user_id)->exists();

        if ($userFrag) {
            $userResult = StageResult::selectRaw("users.id AS user_id,name,IFNULL(title,'') AS title,users.stage_id AS stage_id,icon_id,SUM(score) AS score")
                ->join('users', 'users.id', '=', 'stage_results.user_id')
                ->leftJoin('achievements', 'achievements.id', '=', 'users.achievement_id')
                ->where('users.id', '=', $request->user_id)
                ->groupBy('stage_results.user_id')
                ->first()
                ->attributesToArray();
            $userResult['is_agreement'] = 0;
        }
        // フォロー内でランキング上位の情報を取得する
        $results = StageResult::selectRaw("users.id AS user_id,name,IFNULL(title,'') AS title,users.stage_id AS stage_id,icon_id,SUM(score) AS score")
            ->join('users', 'users.id', '=', 'stage_results.user_id')
            ->join('following_users AS fu', 'users.id', '=', 'fu.following_user_id')
            ->leftJoin('achievements', 'achievements.id', '=', 'users.achievement_id')
            ->where('fu.user_id', '=', $request->user_id)
            ->groupBy("stage_results.user_id")
            ->orderBy('score', 'desc')
            ->limit(100)
            ->get()
            ->toArray();    // 取得するときに配列に変換する

        // 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($results); $i++) {
            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $results[$i]['user_id'])
                ->where('following_user_id', '=', $user->id)->exists();
            $results[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        if ($userFrag) {
            // 返すデータをまとめる
            $results[] = $userResult;
        }

        // scoreを基準に降順に並び替える
        $totalArray = array_column($results, 'score');
        array_multisort($totalArray, SORT_DESC, $results);

        return response()->json($results);
    }
}
