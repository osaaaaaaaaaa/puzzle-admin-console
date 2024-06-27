<!DOCTYPE html>
<html lang="ja">
<head>
    <title>CreateAccount</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body class="bg-body-secondary">
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
<div class="modal modal-sheet position-static d-block p-4 py-md-5" tabindex="-1" role="dialog">
    <div class="modal-dialog pt-5" role="document">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header p-5 pb-4 border-bottom-0">
                <h1 class="fw-bold mb-0 fs-2">■ 管理者登録</h1>
            </div>
            <div class="modal-body p-5 pt-0">
                <form method="post" action="{{ route('accounts.store')}}">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" id="floatingInput"
                               placeholder="アカウント名" name="account_name">
                        <label for="floatingInput">アカウント名</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-3" id="floatingPassword"
                               placeholder="パスワード" name="password">
                        <label for="floatingPassword">パスワード</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-3" id="floatingPassword"
                               placeholder="パスワード(確認用)" name="password_confirmation">
                        <label for="floatingPassword">パスワード(確認用)</label>
                    </div>
                    <button class="w-100 mb-2 btn btn-lg rounded-3 btn-primary" type="submit">登録</button>
                </form>
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
                @if(!empty($error))
                    <ul>
                        <li class="text-danger fw-bold">{{$error}}</li>
                    </ul>
                @endif

            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>
</html>
