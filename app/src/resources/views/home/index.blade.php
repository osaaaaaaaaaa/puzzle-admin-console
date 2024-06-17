<!DOCTYPE html>
<html lang="ja">
<head>
    <title>Home</title>
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
                                        class="nav-link active">所持アイテム</a>
                </li>
            </ul>
            <button type="button" class="btn btn-outline-danger me-2"
                    onclick="location.href='{{ url('accounts/doLogout')}}'">
                ログアウト
            </button>
        </div>
    </div>
</nav>
<div class="container marketing pt-5 p-5">
    <h1 class="mt-5 mb-5 pb-2 border-bottom border-4">■ ホーム</h1>
    <div class="row d-flex justify-content-evenly" style="text-align:center">
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="currentColor"
                 class="mt-3 bi bi-person-badge" viewBox="0 0 16 16">
                <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                <path
                    d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
            </svg>
            <h4 class="fw-normal pt-3">ユーザー一覧</h4>
            <p><a class="btn btn-primary" href="{{ url('accounts/index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="currentColor"
                 class="mt-3 bi bi-people-fill"
                 viewBox="0 0 16 16">
                <path
                    d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
            </svg>
            <h4 class="fw-normal pt-3">プレイヤー一覧</h4>
            <p><a class="btn btn-primary" href="{{ url('players/index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="currentColor"
                 class="mt-3 bi bi-boxes"
                 viewBox="0 0 16 16">
                <path
                    d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434L7.752.066ZM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567L4.25 7.504ZM7.5 9.933l-2.75 1.571v3.134l2.75-1.571V9.933Zm1 3.134 2.75 1.571v-3.134L8.5 9.933v3.134Zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567-2.742 1.567Zm2.242-2.433V3.504L8.5 5.076V8.21l2.75-1.572ZM7.5 8.21V5.076L4.75 3.504v3.134L7.5 8.21ZM5.258 2.643 8 4.21l2.742-1.567L8 1.076 5.258 2.643ZM15 9.933l-2.75 1.571v3.134L15 13.067V9.933ZM3.75 14.638v-3.134L1 9.933v3.134l2.75 1.571Z"/>
            </svg>
            <h4 class="fw-normal pt-3">アイテム一覧</h4>
            <p><a class="btn btn-primary" href="{{ url('items/index')}}">View details »</a></p>
        </div>
        <div class="col-md-2 border rounded-3 bg-body-tertiary">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="currentColor"
                 class="mt-3 bi bi-handbag"
                 viewBox="0 0 16 16">
                <path
                    d="M8 1a2 2 0 0 1 2 2v2H6V3a2 2 0 0 1 2-2zm3 4V3a3 3 0 1 0-6 0v2H3.36a1.5 1.5 0 0 0-1.483 1.277L.85 13.13A2.5 2.5 0 0 0 3.322 16h9.355a2.5 2.5 0 0 0 2.473-2.87l-1.028-6.853A1.5 1.5 0 0 0 12.64 5H11zm-1 1v1.5a.5.5 0 0 0 1 0V6h1.639a.5.5 0 0 1 .494.426l1.028 6.851A1.5 1.5 0 0 1 12.678 15H3.322a1.5 1.5 0 0 1-1.483-1.723l1.028-6.851A.5.5 0 0 1 3.36 6H5v1.5a.5.5 0 1 0 1 0V6h4z"/>
            </svg>
            <h4 class="fw-normal pt-3">所持アイテム一覧</h4>
            <p><a class="btn btn-primary" href="{{ url('inventoryItems/index')}}">View details »</a></p>
        </div>
    </div>
</div>
</body>
</html>
