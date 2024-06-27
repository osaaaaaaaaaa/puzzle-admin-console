<!DOCTYPE html>
<html lang="ja">
<head>
    <title>Mail</title>
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
                <li class="nav-item"><a href="{{ route('users.index')}}" class="nav-link active">ユーザー</a></li>
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
<div class="container p-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="pt-5 pb-3">■ ユーザー受信メール一覧</h1>
            <table class="table table-hover">
                <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>ユーザー名</th>
                    <th>メールID</th>
                    <th>受け取ったかどうか[0:false,1:true]</th>
                    <th>生成日</th>
                    <th>最終更新日</th>
                </tr>
                </thead>
                <tbody class="table-light">
                @if(!empty($mails))
                    @foreach($mails as $data)
                        <tr>
                            <td>{{$data['id']}}</td>
                            <td>{{$data['name']}}</td>
                            <td>{{$data['mail_id']}}</td>
                            <td>{{$data['is_received']}}</td>
                            <td>{{$data['created_at']}}</td>
                            <td>{{$data['updated_at']}}</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            <br>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>
</html>
