<!DOCTYPE html>
<html>
<head>
    <title>Welcome to {{ env('APP_NAME') }}</title>
</head>
<body>
    <h2>Hello, {{ $user->name }}!</h2>
    <p>Your account has been created successfully at {{ env('APP_NAME') }}.</p>
    <p>You can log in to your account using the details below:</p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    
    <p>Please log in using the following link:</p>
    <p><a href="{{ url('/') }}">{{ url('/') }}</a></p>

    <p>If you wish to reset your password, you can do so here:</p>
    <p><a href="{{ route('password.request') }}">{{ route('password.request') }}</a></p>
    
    <br>
    <p>Thanks,</p>
    <p>The {{ env('APP_NAME') }} Team</p>
</body>
</html>
