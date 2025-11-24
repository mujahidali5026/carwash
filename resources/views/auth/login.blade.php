<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        body {
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
        }

        .login-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            margin-top: 5px;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="login-card">
    <div class="login-title">Login</div>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>

        <button class="btn-login">Login</button>

    </form>
</div>

</body>
</html>
