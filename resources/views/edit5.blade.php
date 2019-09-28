<!doctype html>
<html>
<head><title>PetersPlace</title>
    <link href="{{ URL::asset('css/pay.css')}}" rel='stylesheet' media='all' />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family:  sans-serif;
        }

        .sidenav {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: #111;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
        }

        .sidenav a {

            padding: 8px 18px 8px 32px;
            text-decoration: none;
            font-size: 20px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }

        .sidenav a:hover {
            color: #f1f1f1;
        }

        .sidenav .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 16px;
            margin-left: 50px;
        }

        @media screen and (max-height: 450px) {
            .sidenav {padding-top: 15px;}
            .sidenav a {font-size: 18px;}
        }
    </style>
</head>


</head>
</head>

<body>
<div id="main">
    <nav>

        <ul style="display: inline-block">
            <li><a href="#">Home</a></li>
            <li><a href="#">Contact</a>
            <li><a href="#">About</a></li>


        </ul>
    </nav>
</div>

<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <h1 style="padding-left: 29px"> Click here to continue </h1>
    <br>
    <br>
    <a href="/">Customer Details</a>
    <a href="/accoms">Accommodation Details</a>
    <a href="/events1">Event Details</a>
    <a href="/report1">Reports</a>

</div>
<br>

<span style="font-size:20px;cursor:pointer; padding-top: 200px " onclick="openNav()">&#9776;Navigation Panel</span>

<script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
    }

    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
</script>

@extends('layout')
@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>

                        @endforeach
                    </ul>

                </div>
            @endif
            @foreach($events as $events)
                <form action="{{ action('eventscontroller@update', $events->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input class="form-control" type="text" name="c_name" value="{{ $events->c_name}}">
                    </div>

                    <div class="form-group">
                        <label>Event Date</label>
                        <input class="form-control" type="date" name="event_date" value="{{ $events->event_date}}">
                    </div>
                    <div class="form-group">
                        <label>Event Time</label>
                        <input class="form-control" type="time" name="time" value="{{ $events->time}}">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input class="form-control" type="text" name="category" value="{{ $events->category}}">
                    </div>
                    <div class="form-group">
                        <label>No. of Guests</label>
                        <input class="form-control" type="Number" name="guests" value="{{ $events->guests}}">
                    </div>
                    <div class="form-group">
                        <label>Menu ID</label>
                        <input class="form-control" type="text" name="mid" value="{{ $events->mid}}">
                    </div>
                    <div class="form-group">
                        <label>Advancement</label>
                        <input class="form-control" type="Number" name="advance" value="{{ $events->advance}}">
                    </div>
                    <div class="form-group">
                        <label>Total Payment</label>
                        <input class="form-control" type="text" name="total" value="{{ $events->total}}">
                    </div>

                    <button type="submit" class="btn btn-warning">Update</button>
                    <a href="{{ action('eventscontroller@index') }}" class="btn btn-default">Back</a>
                </form>
            @endforeach
        </div>
    </div>
@endsection

</body>
</html>
