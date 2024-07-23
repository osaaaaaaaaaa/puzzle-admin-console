@extends('layouts.app')
@section('title','ItemList')
@section('body')
    <div class="container p-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="pt-5 pb-3">■ レベル一覧</h1>
                @if(!empty($levels))
                    {{$levels->onEachSide(2)->links('vendor.pagination.bootstrap-5')}}
                @endif
                <table class="table table-hover">
                    <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>レベル</th>
                        <th>必要な経験値</th>
                    </tr>
                    </thead>
                    <tbody class="table-light">
                    @if(!empty($levels))
                        @foreach($levels as $data)
                            <tr>
                                <td>{{$data['id']}}</td>
                                <td>{{$data['level']}}</td>
                                <td>{{$data['exp']}}</td>
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
