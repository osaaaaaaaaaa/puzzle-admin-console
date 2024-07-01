@extends('layouts.app')
@section('title','UserMail')
@section('body')
    <div class="container p-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="pt-5 pb-3">■ ユーザー受信メール一覧</h1>
                @if(!empty($mails))
                    {{$mails->onEachSide(2)->links('vendor.pagination.bootstrap-5')}}
                @endif
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
@endsection
