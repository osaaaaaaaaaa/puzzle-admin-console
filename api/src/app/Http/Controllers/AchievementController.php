<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Item;
use App\Models\User;
use App\Models\UserAchievement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AchievementController extends Controller
{
    // アチーブメントマスタと達成状況取得
    public function index(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required']
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

            // 達成状況を取得できた場合
            if (!empty($user_achievement)) {

                // アチーブメントを達成しているかどうか
                $is_achieved = $user_achievement->progress_val >= $achievements[$i]->achieved_val ? 1 : 0;

                // データを格納する
                $response[$i] = [
                    'title' => $achievements[$i]->title,
                    'text' => $achievements[$i]->text,
                    'item_id' => $achievements[$i]->item_id,
                    'item_amount' => $achievements[$i]->item_amount,
                    'achieved_val' => $achievements[$i]->achieved_val,
                    'progress_val' => $user_achievement->progress_val,
                    'is_achieved' => $is_achieved,
                    'is_receive_item' => $user_achievement->is_receive_item,
                    'updated_at' => date_format($user_achievement->updated_at, 'Y-m-d H:i:s')
                ];

            } else {
                // データを格納する
                $response[$i] = [
                    'title' => $achievements[$i]->title,
                    'text' => $achievements[$i]->text,
                    'item_id' => $achievements[$i]->item_id,
                    'item_amount' => $achievements[$i]->item_amount,
                    'achieved_val' => $achievements[$i]->achieved_val,
                    'progress_val' => 0,
                    'is_achieved' => 0,
                    'is_receive_item' => 0,
                    'updated_at' => null
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
            'allie_val' => ['int', 'required']          // 加減算する値
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        User::findOrFail($request->user_id);

        // typeを指定してアチーブメントマスタ取得
        $achievements = Achievement::where('type', $request->type)->get();

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $achievements) {

                foreach ($achievements as $achievement) {

                    // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                    $user_achievement = UserAchievement::firstOrCreate(
                        ['user_id' => $request->user_id, 'achievement_id' => $achievement->id],        // 検索する条件値
                        ['progress_val' => 0, 'is_receive_item' => 0]   // 生成するときに代入するカラム
                    );

                    // アチーブメントを達成していない場合
                    if (!$user_achievement->is_receive_item) {
                        // アチーブメント達成条件値を更新する
                        $total_achievement_value = $user_achievement->progress_val + $request->allie_val;
                        $user_achievement->progress_val = $total_achievement_value <= 0 ? 0 : $total_achievement_value;

                        // アチーブメントの達成条件値に達している場合
                        if ($user_achievement->progress_val >= $achievement->achieved_val) {
                            $user_achievement->progress_val = $achievement->achieved_val;
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
}
