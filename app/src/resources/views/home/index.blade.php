<!DOCTYPE html>
<html lang="ja">
<head>
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .home-icon {
            font-size: 120px;
        }
    </style>
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
<div class="container marketing pt-5 p-5">
    <h1 class="mt-5 mb-5 pb-2 border-bottom border-4">■ マスタデータ</h1>
    <div class="row d-flex flex-row justify-content-evenly" style="text-align:center">
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-envelope-open home-icon"></i>
            <h4 class="fw-normal">メール一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('mails.index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-envelope home-icon"></i>
            <h4 class="fw-normal">メール送信</h4>
            <p><a class="btn btn-primary" href="{{ route('mails.create')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-boxes home-icon"></i>
            <h4 class="fw-normal">アイテム一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('items.index')}}">View details »</a></p>
        </div>
    </div>
    <br>
    <h1 class="mt-5 mb-5 pb-2 border-bottom border-4">■ ユーザーデータ</h1>
    <div class="row d-flex flex-row justify-content-evenly" style="text-align:center">
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-mailbox home-icon"></i>
            <h4 class="fw-normal">受信メール一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('users.mail')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-people-fill home-icon"></i>
            <h4 class="fw-normal">ユーザー一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('users.index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-handbag home-icon"></i>
            <h4 class="fw-normal">所持アイテム一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('inventoryItems.index')}}">View details »</a></p>
        </div>
    </div>
    <br>
    <h1 class="mt-5 mb-5 pb-2 border-bottom border-4">■ アカウント管理</h1>
    <div class="row d-flex flex-row justify-content-evenly" style="text-align:center">
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-person-badge home-icon"></i>
            <h4 class="fw-normal">アカウント一覧</h4>
            <p><a class="btn btn-primary" href="{{ route('accounts.index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <i class="bi bi-person-plus-fill home-icon"></i>
            <h4 class="fw-normal">アカウント登録</h4>
            <p><a class="btn btn-primary" href="{{ route('accounts.create')}}">View details »</a></p>
        </div>
    </div>
</div>
</body>
</html>
