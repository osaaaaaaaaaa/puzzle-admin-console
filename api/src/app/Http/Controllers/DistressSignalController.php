<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReplayResource;
use App\Models\DistressSignal;
use App\Models\FollowingUser;
use App\Models\Guest;
use App\Models\Replay;
use App\Models\User;
use App\Models\UserItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class DistressSignalController extends Controller
{
    // ゲストが参加できる人数
    const GUEST_CNT_MAX = 2;

    // 発信中の救難信号取得
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

        // 指定したユーザーが発信中の救難信号を取得
        $d_signals = DistressSignal::selectRaw('id AS d_signal_id,stage_id')
            ->where('user_id', '=', $request->user_id)->where('action', '=', 0)->get();

        return response()->json($d_signals);
    }

    // 救難信号をランダムに取得
    public function show(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        $user = User::findOrFail($request->user_id);

        // JOINで使うサブクエリを作成する
        $sub_query_guest_cnt = DB::raw('(SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS sq_cnt_guests');

        // 自身が発信していない && ゲストとして参加していない && 未クリア && 参加ゲストの人数がMAX未満の救難信号レコードをランダムに最大10個まで取得する
        $d_signals = DistressSignal::selectRaw("distress_signals.id AS d_signal_id, distress_signals.user_id, stage_id, IFNULL(cnt,0) AS cnt_guest,DATEDIFF(now(),distress_signals.created_at) AS elapsed_days")
            ->leftjoin('guests', 'distress_signals.id', '=', 'guests.distress_signal_id')
            ->leftjoin($sub_query_guest_cnt, 'distress_signals.id', '=', 'sq_cnt_guests.distress_signal_id')
            ->where([
                ['guests.user_id', '!=', $request->user_id],             // ①ゲストとして参加していない
                ['distress_signals.user_id', '!=', $request->user_id],   // ②自身が発信していない
                ['distress_signals.action', '=', 0],                     // ③未クリア
            ])
            ->orWhere([
                ['guests.user_id', '=', null],                           // ④上の①に当てはまるものがない場合
                ['distress_signals.user_id', '!=', $request->user_id],   // ⑤自身が発信していない
                ['distress_signals.action', '=', 0],                     // ⑥未クリア
            ])
            ->having('cnt_guest', '<', self::GUEST_CNT_MAX)
            ->limit(10)
            ->get();

        // ホストのユーザー情報取得 (重複したレコードは省略されるため、collectionで取得する)
        $idList = $d_signals->pluck('user_id')->toArray();
        $users = collect($idList)->map(function ($item) {
            return User::find($item);
        });
        for ($i = 0; $i < count($users); $i++) {
            $d_signals[$i]['host_name'] = $users[$i]->name;
            $d_signals[$i]['icon_id'] = $users[$i]->icon_id;

            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $d_signals[$i]['user_id'])
                ->where('following_user_id', '=', $user->id)->exists();
            $d_signals[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json($d_signals);
    }

    // 救難信号登録
    public function store(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required'],
            'stage_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        User::findOrFail($request->user_id);

        // 重複した救難信号(ゲームクリア済みは除く)が存在する場合はエラー
        $is_d_signal = DistressSignal::where('user_id', '=', $request->user_id)
            ->where('stage_id', '=', $request->stage_id)
            ->where('action', '=', 0)
            ->exists();
        if ($is_d_signal) {
            abort(400);
        }

        try {
            // トランザクション処理
            $d_signal = DB::transaction(function () use ($request) {
                // 登録処理
                return DistressSignal::create([
                    'user_id' => $request->user_id,
                    'stage_id' => $request->stage_id,
                    'action' => 0
                ]);
            });
            return response()->json(['d_signal_id' => $d_signal->id, 'stage_id' => $d_signal->stage_id]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 救難信号ゲームクリア
    public function update(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 救難信号の存在チェック
        $d_signal = DistressSignal::where('id', '=', $request->d_signal_id)->where('action', '=', 0)->first();
        if (empty($d_signal)) {
            abort(400);
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $d_signal) {
                // 更新処理
                $d_signal->action = 1;
                $d_signal->save();
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 救難信号削除
    public function destroy(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 対象の救難信号が存在するかチェック
        $d_signal = DistressSignal::where('id', '=', $request->d_signal_id)->first();
        if (empty($d_signal)) {
            abort(404);
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $d_signal) {

                // 救難信号と関連するゲストの削除処理
                $guests = Guest::where('distress_signal_id', '=', $d_signal->id)->get();
                if (!empty($guests)) {
                    foreach ($guests as $guest) {
                        $guest->delete();
                    }
                }

                // 救難信号と関連するリプレイ情報削除処理
                $replays = Replay::where('distress_signal_id', '=', $d_signal->id)->get();
                if (!empty($replays)) {
                    foreach ($replays as $replay) {
                        $replay->delete();
                    }
                }

                // 救難信号削除処理
                $d_signal->delete();
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 参加ゲスト取得
    public function showGuest(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 救難信号の存在チェック
        $d_signal = DistressSignal::where('id', '=', $request->d_signal_id)->first();
        if (empty($d_signal)) {
            abort(400);
        }

        // ゲストを取得する,ゲストのレコードが存在しなければエラーを返す
        $guests = $d_signal->guests;
        if (count($guests) <= 0) {
            abort(404);
        }

        // 必要なデータを格納する
        $response = [];
        for ($i = 0; $i < count($guests); $i++) {
            $response[$i] = [
                'id' => $guests[$i]->id,
                'user_id' => $guests[$i]->user_id,
                'position' => $guests[$i]->position,
                'vector' => $guests[$i]->vector,
            ];
        }

        // 取得した救難信号レコードを元に参加ゲストの情報を取得する
        return response()->json($response);
    }

    // ゲスト登録・配置情報更新
    public function updateGuest(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'user_id' => ['int', 'min:1', 'required'],
            'position' => ['required'],
            'vector' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 既にゲームクリアしている場合はエラーを返す
        $d_signal = DistressSignal::findOrFail($request->d_signal_id);
        if ($d_signal->action == 1) {
            return response()->json(['error' => "クリア済み"], 400);
        }

        // この救難信号に参加していないユーザーは、救難信号の参加人数がMAXの場合エラーを返される
        $exists = Guest::where('user_id', '=', $request->user_id)->where('distress_signal_id', '=',
            $request->d_signal_id)->exists();
        if (!$exists) {
            $guest_cnt = Guest::where('distress_signal_id', '=', $request->d_signal_id)->count();
            if ($guest_cnt >= self::GUEST_CNT_MAX) {
                return response()->json(['error' => "参加人数がMAX"], 400);
            }
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $guest = Guest::firstOrCreate(
                    ['user_id' => $request->user_id, 'distress_signal_id' => $request->d_signal_id],    // 検索する条件値
                    [
                        'position' => $request->position,
                        'vector' => $request->vector,
                        'is_rewarded' => 0,
                    ]   // 生成するときに代入するカラム
                );

                $guest->position = $request->position;
                $guest->vector = $request->vector;
                $guest->save();

                /*                // 更新するゲストレコードが存在しない場合(初回登録の処理)
                                if (empty($guest)) {

                                    // 現在の参加人数が上限に達している場合はエラー
                                    $guest_cnt = Guest::where('distress_signal_id', '=', $request->d_signal_id)->count();
                                    if ($guest_cnt >= self::GUEST_CNT_MAX) {
                                        abort(404);
                                    }

                                    // 登録処理
                                    Guest::create([
                                        'distress_signal_id' => $request->d_signal_id,
                                        'user_id' => $request->user_id,
                                        'position' => '',
                                        'vector' => '',
                                        'is_rewarded' => 0,
                                    ]);
                                } // 存在する場合は更新処理
                                else {
                                    if (!empty($request->position)) {
                                        $guest->position = $request->position;
                                    }
                                    if (!empty($request->vector)) {
                                        $guest->vector = $request->vector;
                                    }
                                    $guest->save();
                                }*/
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ゲスト削除
    public function destroyGuest(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'user_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 対象の救難信号が存在するかチェック
        DistressSignal::where('id', '=', $request->d_signal_id)->firstOrFail();

        // 指定した救難信号に指定したゲストが存在するかチェック
        $guest = Guest::where('user_id', '=', $request->user_id)
            ->where('distress_signal_id', '=', $request->d_signal_id)
            ->firstOrFail();

        try {
            // トランザクション処理
            DB::transaction(function () use ($guest) {
                // ゲスト削除
                $guest->delete();
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // リプレイ情報取得
    public function showReplay(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'is_latest' => ['boolean', 'required']   // 最新のデータだけを取得するかどうか
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // リプレイ情報取得
        if ($request->is_latest) {
            // 最新のデータ一件を取得する
            $replays = Replay::where('distress_signal_id', '=', $request->d_signal_id)
                ->orderBy('id', 'desc')->take(1)->get();
        } else {
            // 全てのリプレイ情報取得
            $replays = Replay::where('distress_signal_id', '=', $request->d_signal_id)->get();
        }

        // リプレイ情報の存在チェック
        if (count($replays) <= 0) {
            abort(404);
        }

        return response()->json(ReplayResource::collection($replays));
    }

    // リプレイ情報登録
    public function storeReplay(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'replay_data' => ['string', 'required'],
            'guest_data' => ['string', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 指定した救難信号が存在するかどうかチェック
        DistressSignal::findOrFail($request->d_signal_id);

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 登録処理
                Replay::create([
                    'distress_signal_id' => $request->d_signal_id,
                    'replay_data' => $request->replay_data,
                    'guest_data' => $request->guest_data
                ]);
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ホストだったときの救難信号ログ取得
    public function indexHostLog(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザー存在チェック
        User::findOrFail($request->user_id);

        //==========================
        //  救難信号レコード存在チェック
        //==========================

        // JOINで使うサブクエリを作成する
        $sub_query = DB::raw('(SELECT COUNT(*) AS cnt , distress_signal_id FROM guests GROUP BY distress_signal_id) AS sub_query_guests');

        // 救難信号ID,ステージID,action(0:挑戦中,1:ゲームクリア),参加ゲストの人数,救難信号レコードの生成日を取得する
        $d_signals = DistressSignal::selectRaw('distress_signals.id AS d_signal_id, stage_id, action, IFNULL(cnt,0) AS cnt_guest, distress_signals.created_at')
            ->leftjoin($sub_query, 'sub_query_guests.distress_signal_id', '=', 'distress_signals.id')
            ->where('distress_signals.user_id', '=', $request->user_id)
            ->orderByRaw('action, distress_signals.id DESC')
            ->get();
        if (count($d_signals) <= 0) {
            abort(404);
        }

        return response()->json($d_signals);
    }

    // ゲストだったときの救難信号ログ取得
    public function indexGuestLog(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'user_id' => ['int', 'min:1', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザー存在チェック
        $user = User::findOrFail($request->user_id);

        // 自身のゲストレコードの存在チェック
        $guests = Guest::where('user_id', '=', $request->user_id)->get();
        if (empty($guests)) {
            abort(404);
        }

        //==========================
        //  救難信号レコード存在チェック
        //==========================

        // JOINで使うサブクエリを作成する
        $sub_query = DB::raw('(SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS sub_query_guests');

        // 救難信号ID,ホストのID,ステージID,action(0:挑戦中,1:ゲームクリア),参加ゲストの人数,報酬を取得したかどうか,救難信号レコードの生成日を取得する
        $guest_data = Guest::selectRaw('d_signals.id AS d_signal_id, d_signals.user_id AS host_id, stage_id, action, IFNULL(cnt,0) AS cnt_guest, guests.is_rewarded, DATEDIFF(now(),d_signals.created_at) AS elapsed_days')
            ->join('distress_signals AS d_signals', 'guests.distress_signal_id', '=', 'd_signals.id')
            ->leftjoin($sub_query, 'd_signals.id', '=', 'sub_query_guests.distress_signal_id')
            ->where('guests.user_id', '=', $request->user_id)
            ->get();

        // ゲストレコード存在チェック
        if (count($guest_data) <= 0) {
            abort(404);
        }

        // ホストのユーザー情報取得 (重複したレコードは省略されるため、collectionで取得する)
        $idList = $guest_data->pluck('host_id')->toArray();
        $users = collect($idList)->map(function ($item) {
            return User::find($item);
        });
        for ($i = 0; $i < count($users); $i++) {
            $guest_data[$i]['host_name'] = $users[$i]->name;
            $guest_data[$i]['icon_id'] = $users[$i]->icon_id;

            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $guest_data[$i]['host_id'])
                ->where('following_user_id', '=', $user->id)->exists();
            $guest_data[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        return response()->json($guest_data);
    }

    // 救難信号の報酬受け取り
    public function claimReward(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'user_id' => ['int', 'min:1', 'required'],
            'item_id' => ['int', 'min:1', 'required'],
            'item_amount' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ゲームクリアしてあるかチェック
        $d_signal = DistressSignal::findOrFail($request->d_signal_id);
        if ($d_signal->action == 0) {
            abort(404);
        }

        // ゲストの存在チェック
        $guest = Guest::where('user_id', '=', $request->user_id)
            ->where('distress_signal_id', '=', $request->d_signal_id)
            ->where('is_rewarded', '=', 0)
            ->firstOrFail();

        try {
            // トランザクション処理
            DB::transaction(function () use ($request, $guest) {

                // 今回受け取るアイテムに関するレコードが、所持アイテムテーブルに存在するかチェック
                $userItem = UserItem::where('user_id', '=', $request->user_id)
                    ->where('item_id', '=', $request->item_id)->get()->first();

                // アイテムを所持していない場合は登録する
                if (empty($userItem)) {
                    UserItem::create([
                        'user_id' => $request->user_id,
                        'item_id' => $request->item_id,
                        'amount' => $request->item_amount,
                    ]);
                } // 所持アイテムを更新する
                else {
                    $userItem->amount += $request->item_amount;
                    $userItem->save();
                }

                // 報酬を受け取ったことにする
                $guest->is_rewarded = 1;
                $guest->save();
            });
            return response()->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
