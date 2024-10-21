<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistressSignalUserProfileResource;
use App\Http\Resources\GuestResource;
use App\Http\Resources\ReplayResource;
use App\Models\Achievement;
use App\Models\DistressSignal;
use App\Models\FollowingUser;
use App\Models\Guest;
use App\Models\Item;
use App\Models\Replay;
use App\Models\StageResult;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserMail;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use function Laravel\Prompts\select;

class DistressSignalController extends Controller
{
    // 救難信号の上限数を加算するアイテムID
    const ITEM_ADD_DISTRESS_SIGNALS_ID = 36;

    // デフォルトで募集できる上限数
    const DEFAULT_RECRUITMENTS = 1;

    // デフォルトで参加できる上限数
    const DEFAULT_PARTICIPANTS = 2;

    // ゲストが参加できる人数
    const MAX_GUEST_CNT = 2;

    // 救難信号を取得できる最大件数
    const MAX_DISTRESS_SIGNAL = 10;

    // 救難信号の履歴を残せる最大件数
    const MAX_DISTRESS_SIGNAL_HISTORY = 10;

    // ゲストのクリア報酬
    const ITEM_GUEST_REWARD_ID = 37;
    const GUEST_REWARD_AMOUNT = 5;

    // 救難信号の報酬を受け取れるメールID
    const GUEST_REWARD_MAIL_ID = 1;

    // 救難信号に参加しているユーザーのプロフィール取得
    public function showUser(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'user_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($request->user_id);

        // 救難信号の存在チェック
        $d_signal = DistressSignal::where('id', '=', $request->d_signal_id)->firstOrFail();

        // ゲストの取得と存在チェック
        $guests = $d_signal->guests;
        if (count($guests) <= 0) {
            abort(404);
        }

        // ゲストを取得する
        $profiles = User::selectRaw('users.id AS id,name,title_id,icon_id,stage_id')
            ->whereIn('id', $guests->pluck('user_id'))
            ->where('id', '!=', $request->user_id)
            ->get()->toArray();

        // 指定したユーザーがホストではない場合
        if ($d_signal->user_id != $request->user_id) {
            // ホストのユーザー情報取得
            $profile_host = DistressSignal::selectRaw('users.id AS id,name,title_id,icon_id,users.stage_id')
                ->join('users', 'users.id', '=', 'distress_signals.user_id')
                ->where('distress_signals.id', '=', $request->d_signal_id)
                ->get()->toArray();

            $profiles = array_merge($profiles, $profile_host);
        }

        // 設定しているアチーブの称号, 合計スコア, 相互フォローかどうかの情報を格納する
        for ($i = 0; $i < count($profiles); $i++) {

            // アチーブメントの称号取得処理
            $title = '';
            if ($profiles[$i]['title_id'] > 0) {
                $item = Item::selectRaw('name')
                    ->where('id', '=', $profiles[$i]['title_id'])
                    ->first();
                if (!empty($item->name)) {
                    $title = $item->name;
                }
            }
            $profiles[$i]['title'] = $title;

            // 合計スコアを取得する
            $profiles[$i]['score'] = StageResult::selectRaw('SUM(score) AS total_score')
                ->where('stage_results.user_id', '=', $profiles[$i]['id'])->first();
            $profiles[$i]['score'] = empty($profiles[$i]['score']['total_score']) ? 0 : $profiles[$i]['score']['total_score'];

            // 相互フォローかどうかの判定処理
            $isFollow = FollowingUser::where('user_id', '=', $profiles[$i]['id'])
                ->where('following_user_id', '=', $user->id)->exists();
            $profiles[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
        }

        // 取得した救難信号レコードを元に参加ゲストの情報を取得する
        return response()->json(DistressSignalUserProfileResource::collection($profiles));
    }

    // 自身が募集中の救難信号取得
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

        // 相互フォローのユーザーを取得する
        $users = $user->agreement();

        // JOINで使うサブクエリを作成する
        $sub_query_guest_cnt = DB::raw('(SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS sq_cnt_guests');

        // 相互フォローのユーザーが募集している救難信号（参加可能）を取得する
        $response_d_signals = null;
        $d_signal_cnt = 0;
        $usersID = [];
        if (count($users) > 0) {
            $response_d_signals = DistressSignal::selectRaw("distress_signals.id AS d_signal_id, distress_signals.user_id, stage_id, IFNULL(cnt,0) AS cnt_guest,DATEDIFF(now(),distress_signals.created_at) AS elapsed_days")
                ->leftjoin('guests', 'distress_signals.id', '=', 'guests.distress_signal_id')
                ->leftjoin($sub_query_guest_cnt, 'distress_signals.id', '=', 'sq_cnt_guests.distress_signal_id')
                ->whereIn('distress_signals.user_id', $users->pluck('id'))
                ->where(function ($query) use ($request) {
                    $query->where('guests.user_id', '!=', $request->user_id)    // 自身がゲストとして参加していない
                    ->orWhere('guests.user_id', '=', null);                     // まだゲストが存在しない
                })
                ->where([
                    ['distress_signals.user_id', '!=', $request->user_id],   // 自身が発信していない
                    ['distress_signals.action', '=', 0],                     // 未クリア
                ])
                ->having('cnt_guest', '<', self::MAX_GUEST_CNT)
                ->limit(10)
                ->get()->toArray();
            $d_signal_cnt = count($response_d_signals);
            $usersID = $users->pluck('id');
        }

        if ($d_signal_cnt < self::MAX_DISTRESS_SIGNAL) {
            // 件数が足りない場合、他の参加可能な救難信号を取得する
            $d_signals = DistressSignal::selectRaw("distress_signals.id AS d_signal_id, distress_signals.user_id, stage_id, IFNULL(cnt,0) AS cnt_guest,DATEDIFF(now(),distress_signals.created_at) AS elapsed_days")
                ->leftjoin('guests', 'distress_signals.id', '=', 'guests.distress_signal_id')
                ->leftjoin($sub_query_guest_cnt, 'distress_signals.id', '=', 'sq_cnt_guests.distress_signal_id')
                ->whereNotIn('distress_signals.user_id', $usersID)
                ->where(function ($query) use ($request) {
                    $query->where('guests.user_id', '!=', $request->user_id)    // 自身がゲストとして参加していない
                    ->orWhere('guests.user_id', '=', null);                     // まだゲストが存在しない
                })
                ->where([
                    ['distress_signals.user_id', '!=', $request->user_id],   // 自身が発信していない
                    ['distress_signals.action', '=', 0],                     // 未クリア
                ])
                ->having('cnt_guest', '<', self::MAX_GUEST_CNT)
                ->limit(self::MAX_DISTRESS_SIGNAL - $d_signal_cnt)
                ->get()->toArray();
            if (!empty($response_d_signals)) {
                $response_d_signals = array_merge($response_d_signals, $d_signals);
            } else {
                $response_d_signals = $d_signals;
            }
        }

        // ホストのユーザー情報取得 (重複したレコードは省略されるため、collectionで取得する)
        $idList = array_column($response_d_signals, 'user_id');
        $hosts = collect($idList)->map(function ($item) {
            return User::find($item);
        });
        $arrayUsers = $users->toArray();
        for ($i = 0; $i < count($hosts); $i++) {
            $response_d_signals[$i]['host_name'] = $hosts[$i]->name;
            $response_d_signals[$i]['icon_id'] = $hosts[$i]->icon_id;

            // 相互フォローかどうかの判定処理
            $exist = in_array($hosts[$i]->id, array_column($arrayUsers, 'id'));
            $response_d_signals[$i]['is_agreement'] = $exist ? 1 : 0;
        }

        return response()->json($response_d_signals);
    }

    // 救難信号登録
    public function store(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'stage_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        $user = User::findOrFail($request->user()->id);

        // 救難信号を募集した情報を取得
        $recruitments = DistressSignal::where('user_id', '=', $request->user()->id)->get();

        // 募集した数(履歴)が上限に達していないかチェック
        if (self::MAX_DISTRESS_SIGNAL_HISTORY <= count($recruitments)) {
            return response()->json(['error' => "募集した履歴の数が上限に達しています\n\n履歴を削除してください"], 400);
        }

        // 募集する際に、同時に救難信号を募集する数が上限に達していないかチェック
        $current_recruitments = $recruitments->where('action', '=', 0)->count();
        $add_distress_signals = UserItem::where('user_id', '=', $request->user()->id)
            ->where('item_id', '=', self::ITEM_ADD_DISTRESS_SIGNALS_ID)
            ->pluck('amount')->first();
        $add_distress_signals = empty($add_distress_signals) ? 0 : $add_distress_signals;   // 上限の拡張値を取得
        if (self::DEFAULT_RECRUITMENTS + $add_distress_signals <= $current_recruitments) {
            return response()->json(['error' => "同時に募集できる数が上限に達しています"], 400);
        }

        // 重複した救難信号(ゲームクリア済みは除く)が存在する場合はエラー
        $is_d_signal = DistressSignal::where('user_id', '=', $request->user()->id)
            ->where('stage_id', '=', $request->stage_id)
            ->where('action', '=', 0)
            ->exists();
        if ($is_d_signal) {
            return response()->json(['error' => "通信エラーが発生しました"], 400);
        }

        try {
            // トランザクション処理
            $d_signal = DB::transaction(function () use ($request) {
                // 登録処理
                return DistressSignal::create([
                    'user_id' => $request->user()->id,
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

        // 救難信号の存在チェック(クリア済みの場合はエラーを返す)
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

                // 救難信号と関連するゲストを取得する
                $guests = Guest::where('distress_signal_id', '=', $d_signal->id)->get();
                if (!empty($guests)) {
                    foreach ($guests as $guest) {

                        if ($d_signal->action && !$guest->is_rewarded) {
                            // 救難信号のステージがクリア済 && ゲストがまだ報酬を受け取っていない場合はメールを送信する
                            UserMail::create([
                                'user_id' => $guest->user_id,
                                'mail_id' => self::GUEST_REWARD_MAIL_ID,
                                'is_received' => 0
                            ]);
                        }

                        // ゲスト削除処理
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

        for ($i = 0; $i < count($guests); $i++) {
            $guests[$i]['name'] = User::where('id', '=', $guests[$i]->user_id)->pluck('name')->first();
        }

        // 取得した救難信号レコードを元に参加ゲストの情報を取得する
        return response()->json(GuestResource::collection($guests));
    }

    // ゲスト登録・配置情報更新
    public function updateGuest(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'position' => ['required'],
            'vector' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // ユーザーの存在チェック
        $user = User::findOrFail($request->user()->id);

        // 既にゲームクリアしている場合はエラーを返す
        $d_signal = DistressSignal::findOrFail($request->d_signal_id);
        if ($d_signal->action == 1) {
            return response()->json(['error' => "参加できませんでした"], 400);
        }

        // ゲスト登録するのかどうか取得(まだレコードが存在しない場合は登録)
        $exists = Guest::where('user_id', '=', $request->user()->id)
            ->where('distress_signal_id', '=', $request->d_signal_id)->exists();
        if (!$exists) {
            // 参加した数(履歴)が上限に達していないかチェック
            $participants = Guest::where('user_id', '=', $request->user()->id)->get();
            if (self::MAX_DISTRESS_SIGNAL_HISTORY <= count($participants)) {
                return response()->json(['error' => "参加した履歴の数が上限に達しています\n\n履歴を削除してください"],
                    400);
            }

            // 参加する際に、同時に救難信号に参加している数が上限に達していないかチェック(未クリアの救難信号に参加している数を取得する)
            $current_recruitments = DistressSignal::whereIn('id', $participants->pluck('distress_signal_id'))
                ->where('action', '=', 0)->count();
            $add_distress_signals = UserItem::where('user_id', '=', $request->user()->id)
                ->where('item_id', '=', self::ITEM_ADD_DISTRESS_SIGNALS_ID)
                ->pluck('amount')->first();
            $add_distress_signals = empty($add_distress_signals) ? 0 : $add_distress_signals;   // 上限の拡張値を取得
            if (self::DEFAULT_PARTICIPANTS + $add_distress_signals <= $current_recruitments) {
                return response()->json(['error' => "同時に参加できる数が上限に達しています"], 400);
            }

            // この救難信号に参加していないユーザーは、救難信号の参加人数がMAXの場合エラーを返される
            $guest_cnt = Guest::where('distress_signal_id', '=', $request->d_signal_id)->count();
            if ($guest_cnt >= self::MAX_GUEST_CNT) {
                return response()->json(['error' => "参加できませんでした"], 400);
            }
        }

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $guest = Guest::firstOrCreate(
                    ['user_id' => $request->user()->id, 'distress_signal_id' => $request->d_signal_id],    // 検索する条件値
                    [
                        'position' => $request->position,
                        'vector' => $request->vector,
                        'is_rewarded' => 0,
                    ]   // 生成するときに代入するカラム
                );

                $guest->position = $request->position;
                $guest->vector = $request->vector;
                $guest->save();
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
            'destroy_user_id' => ['int', 'min:1', 'required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 対象の救難信号が存在するかチェック
        DistressSignal::where('id', '=', $request->d_signal_id)->firstOrFail();

        // 指定した救難信号に指定したゲストが存在するかチェック
        $guest = Guest::where('user_id', '=', $request->destroy_user_id)
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
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // リプレイ情報取得&存在チェック
        $replays = Replay::where('distress_signal_id', '=', $request->d_signal_id)->firstOrFail();

        return response()->json(ReplayResource::make($replays));
    }

    // リプレイ情報更新
    public function updateReplay(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'd_signal_id' => ['int', 'min:1', 'required'],
            'replay_data' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 指定した救難信号が存在するかどうかチェック
        DistressSignal::findOrFail($request->d_signal_id);

        try {
            // トランザクション処理
            DB::transaction(function () use ($request) {
                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $replay = Replay::firstOrCreate(
                    ['distress_signal_id' => $request->d_signal_id],    // 検索する条件値
                    ['replay_data' => json_encode($request->replay_data),]   // 生成するときに代入するカラム
                );
                $replay->replay_data = json_encode($request->replay_data);
                $replay->save();
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
        $guest = Guest::where('user_id', '=', $request->user()->id)
            ->where('distress_signal_id', '=', $request->d_signal_id)
            ->where('is_rewarded', '=', 0)
            ->firstOrFail();

        try {
            // トランザクション処理
            $userItem = DB::transaction(function () use ($request, $guest) {

                // 条件値に一致するレコードを検索して返す、存在しなければ新しく生成して返す
                $userItem = UserItem::firstOrCreate(
                    ['user_id' => $request->user()->id, 'item_id' => self::ITEM_GUEST_REWARD_ID],
                    // 検索する条件値
                    ['amount' => 0]   // 生成するときに代入するカラム
                );

                $userItem->amount += self::GUEST_REWARD_AMOUNT;
                $userItem->save();

                // 報酬を受け取ったことにする
                $guest->is_rewarded = 1;
                $guest->save();

                return $userItem;
            });
            $item = Item::where('id', '=', self::ITEM_GUEST_REWARD_ID)->firstOrFail();
            $responseItem = [
                'item_id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'effect' => $item->effect,
                'description' => $item->description,
                'amount' => self::GUEST_REWARD_AMOUNT,
            ];
            return response()->json($responseItem);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
