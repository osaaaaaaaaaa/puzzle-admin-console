@extends('layouts.app')
@section('title','UsersFollow')
@section('body')
    <div class="container p-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="pt-5 pb-3">■ フォローリスト</h1>
                <form class="d-flex pb-3" role="search" method="get" action="{{ route('users.follow.show')}}">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <input name="id" class="form-control me-2 border-3" type="search"
                                   placeholder="ユーザーIDを入力"
                                   aria-label="Search">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-success btn-gradient border-2" type="submit">検索</button>
                        </div>
                    </div>
                </form>
                @if(!empty($items))
                    {{$items->onEachSide(2)->links('vendor.pagination.bootstrap-5')}}
                @endif
                <table class="table table-hover">
                    <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>ユーザー名</th>
                        <th>相手の名前</th>
                        <th>相互フォロー(0:false,1:true)</th>
                        <th>生成日</th>
                        <th>最終更新日</th>
                    </tr>
                    </thead>
                    <tbody class="table-light">
                    @if(!empty($users))
                        @foreach($users as $user)
                            <tr>
                                <td>{{$user->id}}</td>
                                <td>{{$user->user_name}}</td>
                                <td>{{$user->following_name}}</td>
                                <td>{{$user->is_agreement}}</td>
                                <td>{{$user->created_at}}</td>
                                <td>{{$user->updated_at}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
