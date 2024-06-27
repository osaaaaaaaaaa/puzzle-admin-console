<!DOCTYPE html>
<html lang="ja">
<head>
    <title>MailCrate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-secondary">
    <div class="container-fluid">
        <a class="navbar-brand text-warning fw-bold fs-4" href="{{ route('home.index')}}">パズルゲーム管理サイト</a>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item"><a href="{{ route('accounts.index')}}" class="nav-link active">アカウント</a>
                </li>
                <li class="nav-item"><a href="{{ route('users.index')}}" class="nav-link active">プレイヤー</a></li>
                <li class="nav-item"><a href="{{ route('items.index')}}" class="nav-link active">アイテム</a></li>
                <li class="nav-item"><a href="{{ route('inventoryItems.index')}}"
                                        class="nav-link active">所持アイテム</a>
                </li>
            </ul>
            <button type="button" class="btn btn-outline-danger me-2"
                    onclick="location.href='{{ route('auths.dologout')}}'">
                ログアウト
            </button>
        </div>
    </div>
</nav>
<div class="container my-3">
    <main>
        <div class="py-5 pt-5 text-center">
            <h2 class="pt-5">一斉メール送信</h2>
            <p class="lead">Below is an example form built entirely with Bootstrap’s form controls. Each required form
                group has a validation state that can be triggered by attempting to submit the form without completing
                it.</p>
        </div>

        <div class="row">
            <div>
                <h4 class="mb-3">メール作成</h4>
                <form class="needs-validation" method="post" action="{{ route('mails.store')}}">
                    @csrf
                    <div class="col-md-12">
                        <label for="text" class="form-label">テキスト</label>
                        <textarea id="textarea" class="form-control" name="text"
                                  placeholder="テキストを入力" disabled>{{$request['text']}}</textarea>
                    </div>
                    <br>

                    <div class="col-md-2 py-1">
                        <label for="number" class="form-label">アイテムの種類数</label>
                        <input type="number" id="type_cnt" name="type_cnt" class="form-control" max="5" min="0"
                               value="{{$request->type_cnt}}" disabled>
                    </div>
                    <br>

                    <hr class="my-1 pb-3">

                    {{--アイテムの種類とアイテムの個数--}}
                    <h4 class="mb-3">アイテム情報を設定</h4>
                    <div id="item_container">
                    </div>
                    <br>

                    <hr class="my-4">
                    @if($errors->any())
                        <ul>
                            @foreach($errors->all() as $text)
                                <li class="text-danger fw-bold">{{$text}}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($result))
                        <ul>
                            <li class="text-success fw-bold">{{$result}}</li>
                        </ul>
                    @endif

                    <button class="w-100 btn btn-primary btn-lg my-3 pt-2" type="submit">送信</button>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
<script>
    //==================================================
    // アイテムの種類数の値に応じてアイテムのフォームをセットする
    //==================================================

    const itemContainer = document.getElementById('item_container');

    // アイテムフォームを作成するメソッド
    function createItemForm(loopMax, loopStartIndex) {

        if (loopMax === 0) return;

        let item_data = {{$request->item_data}};
        let items = {{$items}};
        let itemID;
        let itemCnt;
        let itemName;
        for (let i = loopStartIndex, html; i < loopMax; i++) {

            itemID = item_data[i]['item_id'];
            itemCnt = item_data[i]['item_cnt'];
            itemName = items[itemID]['item_name'];

            html = itemContainer.innerHTML;
            itemContainer.innerHTML = html +
                '<div class="row py-3 item_ele">' +
                '<div class="col-md-5">' +
                '<label for="state" class="form-label">[' + (i + 1) + ']アイテムの種類</label>' +
                ' class="form-select" id="state" name="item_id' + (i + 1) + '" disabled>' +
                '<option value="' + itemID + '">' + itemName + '</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<label for="number" class="form-label">[' + (i + 1) + ']アイテムの個数</label>' +
                '<input disabled type="number" id="number" name="item_cnt' + (i + 1) + '" class="form-control" max="10" min="1" value="' + itemCnt + '">' +
                '</div>'
            '</div><br>';
        }
    }

    @if(!empty($request->type_cnt))
    // アイテムフォームを作成する
    createItemForm({{$request->type_cnt}}, 0);
    @endif

</script>
</body>
</html>
