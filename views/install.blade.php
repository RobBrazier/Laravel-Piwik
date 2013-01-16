<!DOCTYPE html>
<html lang="en">
<head>
    <title>Laravel-Piwik Bundle Installer</title>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Laravel-Piwik Bundle Installer</h1>
        @if($errors->messages)
            <div class="alert alert-error">
            @foreach($errors->messages as $error)
                {{ $error[0] }}<br/>
            @endforeach
            </div>
        @endif
        {{ Form::open() }}
        {{ Form::label('piwik_url', 'Piwik URL: ') }}
        {{ Form::url('piwik_url', Input::old('piwik_url')) }}
        <hr>
        <p><strong>Option 1:</strong></p>
        {{ Form::label('username', 'Username: ') }}
        {{ Form::text('username', Input::old('username')) }}
        {{ Form::label('password', 'Password: ') }}
        {{ Form::password('password') }}
        <p><strong>Option 2:</strong></p>
        {{ Form::label('api_key', 'API Key: ') }}
        {{ Form::text('api_key', Input::old('api_key')) }}
        <hr>
        {{ Form::label('format', 'Format: ') }}
        {{ Form::select('format', array('php'=>'PHP', 'xml'=>'XML', 'json'=>'JSON', 'html'=>'HTML', 'rss'=>'RSS', 'original'=>'Original'), Input::old('format')); }}
        {{ Form::label('period', 'Period: ') }}
        {{ Form::select('period', array('today'=>'Today', 'yesterday'=>'Yesterday', 'previous7'=>'Previous 7 days (not including today)', 'previous30'=>'Previous 30 days (not including today)', 'last7'=>'Last 7 days (including today)', 'last30'=>'Last 30 days (including today)', 'currentweek'=>'Current Week', 'currentmonth'=>'Current Month', 'currentyear'=>'Current Year'), Input::old('period')); }}
        {{ Form::label('site_id', 'Site ID: ') }}
        {{ Form::text('site_id', Input::old('site_id')) }}<br/>
        {{ Form::submit('Submit', array('class'=>'btn-primary')) }}
        {{ Form::close() }}

    </div>
</body>
</html>