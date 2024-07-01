@extends('layouts.app')
@section('title','InventoryItemList')
@section('body')
    <div class="container p-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="pt-5 pb-3">■ ユーザーの所持アイテム一覧</h1>
                <form class="d-flex pb-3" role="search" method="post" action="{{ url('inventoryItems/index')}}">
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
                @if(!empty($inventory_items))
                    {{$inventory_items->onEachSide(2)->links('vendor.pagination.bootstrap-5')}}
                @endif
                <table class="table table-hover">
                    <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>ユーザー名</th>
                        <th>アイテム名</th>
                        <th>所持個数</th>
                    </tr>
                    </thead>
                    <tbody class="table-light">
                    @if(!empty($inventory_items))
                        @foreach($inventory_items as $data)
                            <tr>
                                <td>{{$data->id}}</td>
                                <td>{{$data->user_name}}</td>
                                <td>{{$data->item_name}}</td>
                                <td>{{$data->item_cnt}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
