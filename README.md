# 目的
---------------------------------------------------
* カジュアルパズルゲームのWeb管理ツールの作成
* カジュアルパズルゲームで使用するWebAPIの作成
  * 送受信するデータをJSON文字列化
  * [クライアント] HTTP通信リクエストを出してサーバーにSQL発行
  * [サーバー] 発行されたSQLを元にレスポンスする 

# 概要
---------------------------------------------------

## ER図
![パズルゲームER図](https://github.com/user-attachments/assets/6b71e7f2-bd1a-47ff-99f6-71405dc33f94)

## 作成する管理ツールの機能
* マスタデータ管理
    - [x] アイテム一覧
    - [x] レベル一覧
    - [x] アチーブメント一覧
    - [x] アチーブメント作成ページ
 
          
* ログデータ管理
    - [x] フォローログ一覧
    - [x] アイテムログ一覧
    - [x] メールログ一覧


* 救難信号データ管理
    - [x] 救難信号一覧


* ユーザーデータ管理
    - [x] ユーザー一覧
    - [x] 所持アイテム一覧
    - [x] フォロー一覧
    - [x] 受信メール一覧
    - [x] アチーブメントの達成状況一覧


* メールデータ管理
    - [x] メール一覧
    - [x] メール作成・送信ページ

  
## 作成するAPI
* ユーザーデータ関連
    - [x] ユーザー情報の登録・取得・更新API
    - [x] 所持アイテムリストの取得・更新(更新するアイテムのレコードがなければ登録する)API
    - [x] フォローリストの登録・取得・解除API
    - [x] 受信メールリストの取得・開封API
    - [ ] アチーブメント達成状況更新(更新するレコードがなければ登録する)API  


* アチーブメント関連  
    - [ ] アチーブメントの取得API  
    - [ ] アチーブメントの報酬受け取りAPI  


* 救難信号データ関連  
    - [x] 救難信号の取得・更新(更新する救難信号のレコードがなければ登録する)API  
          - ゲストが参加人数が上限に満たない救難信号をランダムに取得する  
          - 救難信号を後で更新する場面：ホストがステージクリアしたときに`action`を`1（ステージクリア）`に更新する

          
    - [x] 削除したい救難信号と、それに関連するレコードを削除するAPI  
          - ホストが救難信号をキャンセルしたときに使用  
          - 関連するレコード : ゲストレコード、リプレイレコード

          
    - [x] 救難信号に参加するゲストの更新(更新するゲストのレコードがなければ登録する)API  
          - 自身のプレイヤーの配置情報とベクトルを更新できる

          
    - [x] ホストのリプレイ情報登録・取得するAPI

 
    - [ ] ホストで参加したときの救難信号ログの取得  
          - 参加したゲスト、リプレイ情報を確認できる  

          
    - [ ] ゲストで参加したときの救難信号ログの取得      
          - 参加したホスト・ゲスト、リプレイ情報を確認できる  
          - ホストがまだステージをクリアできていない場合、自身のプレイヤーを再配置できる  
          - ホストがステージをクリアしていた場合、報酬を受け取ることができる  

