<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserRewardItemResource;
use App\Models\Achievement;
use App\Models\Item;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AchievementController extends Controller
{
    const ITEM_POINT_ID = 37;

    // アチーブメントマスタと達成状況取得
    public function index(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        User::findOrFail($request->user_id);

        // アチーブメントマスタを取得
        $achievements = Achievement::All();

        // 返すデータを格納する
        $response = [];
        for ($i = 0; $i < count($achievements); $i++) {
            // アチーブメント達成状況取得
            $user_achievement = UserAchievement::where('user_id', '=', $request->user_id)
                ->where('achievement_id', '=', $achievements[$i]->id)->first();

            // アイテム情報を取得
            $item = Item::where('id', '=', $achievements[$i]->item_id)->first();

            // 達成状況を取得できた場合
            if (!empty($user_achievement)) {

                // アチーブメントを達成しているかどうか
                $is_achieved = $user_achievement->progress_val >= $achievements[$i]->achieved_val ? 1 : 0;

                // データを格納する
                $response[$i] = [
                    'achievement_id' => $achievements[$i]->id,
                    'text' => $achievements[$i]->text,
                    'type' => $achievements[$i]->type,
                    'achieved_val' => $achievements[$i]->achieved_val,
                    'progress_val' => $user_achievement->progress_val,
                    'is_achieved' => $is_achieved,
                    'is_receive_item' => $user_achievement->is_receive_item,
                    'item' => [
                        'item_id' => $achievements[$i]->item_id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'effect' => $item->effect,
                        'description' => $item->description,
                        'amount' => $achievements[$i]->item_amount
                    ]
                ];

            } else {
                // データを格納する
                $response[$i] = [
                    'achievement_id' => $achievements[$i]->id,
                    'text' => $achievements[$i]->text,
                    'type' => $achievements[$i]->type,
                    'achieved_val' => $achievements[$i]->achieved_val,
                    'progress_val' => 0,
                    'is_achieved' => 0,
                    'is_receive_item' => 0,
                    'item' => [
                        'item_id' => $achievements[$i]->item_id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'effect' => $item->effect,
                        'description' => $item->description,
                        'amount' => $achievements[$i]->item_amount
                    ]
                ];
            }
        }

        return response()->json($response);
    }

    // アチーブメント達成状況更新
    public function update(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required'],  // ユーザーID
            'type' => ['int', 'min:1', 'required'],     // アチーブメントの種類
            'allie_val' => ['int', 'required']          // 加減算する値(typeが2のときは0を指定)
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // typeがアチーブメント報酬の場合はエラー
        if ($request->type == 3) {
            abort(400);
        }

        // ユーザーの存在チェック
        $user = User::findOrFail($request->user_id);

        // typeを指定してアチーブメントマスタ取得
        $achievements = Achievement::where('type', $request->type)->get();
        if ($achievements->count() == 0) {
            abort(400);
        }

        // トータルスコアを取得する
        $total_score = 0;
        if ($request->type == 2) {
            $total_score = $user->totalscore()->first() == null ? 0 : $user->totalscore()->pluck('total_score')->first();
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $achievements, $total_score) {
                foreach ($achievements as $achievement) {

                    // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                    $user_achievement = UserAchievement::firstOrCreate(
                        ['user_id' => $request->user_id, 'achievement_id' => $achievement->id],
                        // 検索する条件値
                        ['progress_val' => 0, 'is_receive_item' => 0]   // 生成するときに代入するカラム
                    );

                    // アチーブメントを報酬を受け取っていない場合
                    if (!$user_achievement->is_receive_item) {
                        // アチーブメント達成進捗値を更新する
                        if ($request->type == 1) {
                            // 初回ステージクリアの場合
                            $total_achievement_value = $user_achievement->progress_val + $request->allie_val;
                            $user_achievement->progress_val = $total_achievement_value <= 0 ? 0 : $total_achievement_value;
                        } elseif ($request->type == 2) {
                            // トータルスコアの場合
                            $user_achievement->progress_val = $total_score;
                        }

                        // アチーブメントの達成条件値に達している場合
                        if ($user_achievement->progress_val >= $achievement->achieved_val) {
                            $user_achievement->progress_val = $achievement->achieved_val;

                            // 自動で報酬受け取り
                            $userItem = UserItem::firstOrCreate(
                                ['user_id' => $request->user_id, 'item_id' => $achievement->item_id],
                                // 検索する条件値
                                ['amount' => 0]
                            );
                            $userItem->amount += $achievement->item_amount;
                            $userItem->save();

                            // アチーブメント報酬を受け取ったことにする
                            $user_achievement->is_receive_item = 1;
                        }
                        $user_achievement->save();
                    }
                }
            });

            return response()->json();

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // アチーブメント報酬受け取り処理
    public function receive(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'achievement_id' => ['required', 'int'],
            'user_id' => ['required', 'int']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 指定したユーザーが存在するかどうか
        $user = User::findOrFail($request->user_id);

        // アチーブメント存在チェック
        $achievement = Achievement::where('id', '=', $request->achievement_id)->firstOrFail();

        // 合計所持ポイントを取得する
        $get_point = $user->totalpoint()->where('item_id', '=', self::ITEM_POINT_ID)->pluck('amount')->first();
        $total_point = $get_point == null ? 0 : $get_point;

        // ユーザーアチーブメントが受け取り済みかどうかチェック
        $userAchievement = UserAchievement::firstOrCreate(
            ['user_id' => $request->user_id, 'achievement_id' => $request->achievement_id],
            // 検索する条件値
            ['progress_val' => $total_point, 'is_receive_item' => 0]   // 生成するときに代入するカラム
        );
        if ($userAchievement->is_receive_item === 1) {
            abort(404);
        }

        try {
            // トランザクション処理
            $response = DB::transaction(function () use ($request, $achievement, $userAchievement, $total_point) {

                // 達成条件を満たしているかチェック
                $userAchievement->progress_val = $total_point > $achievement->achieved_val ? $achievement->achieved_val : $total_point;
                if ($total_point < $achievement->achieved_val) {
                    $userAchievement->save();
                    abort(404);
                }

                // 報酬アイテムを取得
                $item = $achievement->items->first();
                $item['amount'] = $achievement->item_amount;
                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $userItem = UserItem::firstOrCreate(
                    ['user_id' => $request->user_id, 'item_id' => $item->id],
                    // 検索する条件値
                    ['amount' => 0]   // 生成するときに代入するカラム
                );

                $amount = $userItem->amount + $item->amount;
                $userItem->amount = $amount > 0 ? $amount : 0;
                $userItem->save();

                // アイテムを受け取り済みにする
                $userAchievement->is_receive_item = 1;
                $userAchievement->save();

                return $item;
            });

            if (empty($response)) {
                return response()->json();
            }
            return response()->json(UserRewardItemResource::make($response));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
