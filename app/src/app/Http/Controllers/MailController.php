<?php

namespace App\Http\Controllers;

use App\Models\Attached_Item;
use App\Models\Item;
use App\Models\Mail;
use App\Models\Received_Mail;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{
    // メール一覧表示
    public function index(Request $request)
    {
        // メールマスタ取得する
        $mails = Mail::all();
        // 最終的なデータを格納する
        $mailData = [];

        for ($i = 0; $i < count($mails); $i++) {
            // 添付アイテムを取得する
            $attached_items = Attached_Item::selectRaw('item_name AS name, cnt')
                ->join('items', 'items.id', '=', 'attached__items.item_id')
                ->where('mail_id', '=', ($i + 1))
                ->get();

            // アイテム情報を結合する
            $itemData = '';
            foreach ($attached_items as $item) {
                $itemData = $itemData . $item['name'] . '×' . $item['cnt'] . ' , ';
            }

            // データを格納する
            $array = [
                [
                    'id' => $mails[$i]['id'],
                    'text' => $mails[$i]['text'],
                    'item' => $itemData,
                    'created_at' => $mails[$i]['created_at'],
                    'updated_at' => $mails[$i]['updated_at']
                ]
            ];
            $mailData = $mailData + $array;
        }

        return view('mails/index', ['mailData' => $mailData]);
    }

    // メール送信ページの表示
    public function create(Request $request)
    {
        // アイテム情報を取得する
        $items = Item::All();

        return view('mails/create', ['items' => $items, 'normally' => $request['normally']]);
    }

    // メール作成処理
    public function store(Request $request)
    {
//        // intに変換できるかどうか
//        if (is_numeric($request['type_cnt'])) {
//
//            $request['item_data'] = [];
//
//            // アイテムの種類数分追加する
//            for ($i = 0; $i < (int)$request['type_cnt']; $i++) {
//
//                $id = 'item_id' . ($i + 1);
//                $cnt = 'cnt' . ($i + 1);
//                $request['item_data'] += [
//                    ['item_id' => $request[$id], 'item_cnt' => $request[$cnt]]
//                ];
//            }
//        }

        // カスタムバリデーション
        $validator = Validator::make($request->all(), [
            'text' => ['required'],
            'type_cnt' => ['required']
        ]);

        if ($validator->fails()) {
            return redirect()->route('mails.create')
                ->withErrors($validator)
                ->withInput();
        }

        //==============
        // 送信処理
        //==============
        $mailID_max = Mail::max('id');  // 最新のメールID

        // メールマスタにレコードを追加
        Mail::create(['text' => $request->text]);

        // 添付するアイテムをレコードに追加
        if ($request->type_cnt > 0) {
            for ($i = 0; $i < $request->type_cnt; $i++) {
                $id = 'item_id' . ($i + 1);
                $cnt = 'item_cnt' . ($i + 1);
                Attached_Item::create([
                    'mail_id' => ($mailID_max + 1),
                    'item_id' => $request[$id],
                    'cnt' => $request[$cnt]
                ]);
            }
        }

        // ユーザーが受け取ったかどうかのテーブルにレコードを追加する
        $users = User::All();
        foreach ($users as $user) {
            Received_Mail::create(['user_id' => $user['id'], 'mail_id' => ($mailID_max + 1), 'is_received' => 0]);
        }

        return redirect()->route('mails.create', ['normally' => 'valid']);
    }

    // 確認画面の表示
    public function confirm(Request $request)
    {
        // アイテム情報を取得する
        $items = Item::All();

        return view('mails/confirm', ['items' => $items, 'request' => $request]);
    }
}
