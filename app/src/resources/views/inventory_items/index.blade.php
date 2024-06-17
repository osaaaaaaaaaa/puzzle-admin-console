<!DOCTYPE html>
<html lang="ja">
<head>
    <title>InventoryItemList</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-secondary">
    <div class="container-fluid">
        <a class="navbar-brand text-warning fw-bold fs-4" href="{{ url('home/index')}}">パズルゲーム管理サイト</a>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item"><a href="{{ url('accounts/index')}}" class="nav-link active">ユーザー</a></li>
                <li class="nav-item"><a href="{{ url('players/index')}}" class="nav-link active">プレイヤー</a></li>
                <li class="nav-item"><a href="{{ url('items/index')}}" class="nav-link active">アイテム</a></li>
                <li class="nav-item"><a href="{{ url('inventoryItems/index')}}"
                                        class="nav-link disabled">所持アイテム</a>
                </li>
            </ul>
            <button type="button" class="btn btn-outline-danger me-2"
                    onclick="location.href='{{ url('accounts/doLogout')}}'">
                ログアウト
            </button>
        </div>
    </div>
</nav>
<div class="container p-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="pt-5 pb-3">■ プレイヤーの所持アイテム一覧</h1>
            <form class="d-flex" role="search" method="post" action="./">
                <div class="row">
                    <div class="col-md-8">
                        <input name="player_name" class="form-control me-2 border-3" type="search"
                               placeholder="プレイヤー名を入力"
                               aria-label="Search">
                        <label><input hidden="hidden" name="action" value="inventoryItemList"></label>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-success btn-gradient border-2" type="submit">検索</button>
                    </div>
                </div>
            </form>
            <table class="table table-hover">
                <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>プレイヤー名</th>
                    <th>アイテム名</th>
                    <th>所持個数</th>
                </tr>
                </thead>
                <tbody class="table-light">
                @if(!empty($dataList))
                    @foreach($dataList as $data)
                        <tr>
                            <td>{{$data['id']}}</td>
                            <td>{{$data['player_name']}}</td>
                            <td>{{$data['item_name']}}</td>
                            <td>{{$data['item_cnt']}}</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>
</html>
