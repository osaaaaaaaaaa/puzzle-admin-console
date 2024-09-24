@extends('layouts.app')
@section('title','ItemLogList')
@section('body')
    <div class="container p-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="pt-5 pb-3">■ アイテムログ一覧</h1>
                <form class="d-flex pb-3" role="search" method="get"
                      action="{{ route('logs.item.show')}}">
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
                @if(!empty($logs))
                    {{$logs->onEachSide(2)->links('vendor.pagination.bootstrap-5')}}
                @endif
                <table class="table table-hover">
                    <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>ユーザー名</th>
                        <th>アイテム名</th>
                        <th>入手方法</th>
                        <th>増減数</th>
                        <th>生成日</th>
                    </tr>
                    </thead>
                    <tbody class="table-light">
                    @if(!empty($logs))
                        @foreach($logs as $data)
                            <tr>
                                <td>{{$data->pivot->id}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$data->name}}</td>
                                <td>
                                    @switch($data->pivot->option_id)
                                        @case(1)
                                            アチーブメント報酬
                                            @break
                                        @case(2)
                                            メール受け取り
                                            @break
                                    @endswitch
                                </td>
                                <td>{{$data->pivot->allie_count}}</td>
                                <td>{{$data->pivot->created_at}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <br>
            </div>
        </div>
    </div>
@endsection
