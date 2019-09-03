<!DOCTYPE html>
<html lang="en">
<head>
    <link href="{{URL::asset('css/indexs.css')}}" rel="stylesheet " media="all">
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
@extends('myview')

@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            @if($message = $session::get('danger'))
                <div class="alert alert-danger">
                    <strong>Input data failed, please try again</strong>
                </div>
            @endif
            <form action="{{ action('utilitycontroller@store') }}" method="post">
                @csrf
                <div class="form-group">
                    <label>Date</label>
                    <input class="form-control" type="date" name="name"/>
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input class="form-control" type="text" name="name"/>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <input class="form-control" type="text" name="name"/>
                </div>
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
    </div>
@endsection
</body>
</html>
