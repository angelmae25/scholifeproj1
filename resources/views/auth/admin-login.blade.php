
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholife — Admin Dashboard</title>
    <style>
        html { scrollbar-width: none; -ms-overflow-style: none; }
        html::-webkit-scrollbar { display: none; width: 0; height: 0; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #fdf8f3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 14px;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            color: #555;
            margin-top: 0;
            letter-spacing: 0.4px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 36px 40px 32px;
            width: 340px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .card h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .card p {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #8b1c2c;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1.5px solid #c9999f;
            border-radius: 6px;
            padding: 9px 12px;
            font-size: 0.9rem;
            color: #1a1a1a;
            background: #fff8f8;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 16px;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #8b1c2c;
            background: #fff;
        }

        input.is-invalid { border-color: #e53e3e; }

        .error-msg {
            font-size: 0.75rem;
            color: #e53e3e;
            margin-top: -12px;
            margin-bottom: 12px;
            display: block;
        }

        .btn-signin {
            width: 100%;
            padding: 11px;
            background: #8b1c2c;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }

        .btn-signin:hover  { background: #6e1522; }
        .btn-signin:active { background: #5a111b; }
    </style>
</head>
<body>

<div class="brand">
    <img src="{{ asset('login-logo.png') }}"
         style="width:100px;height:auto;object-fit:contain"
         alt="Scholife">
    <div class="brand-subtitle" style="margin-top:6px;font-size:.88rem;color:#555;letter-spacing:.4px">
        Admin Dashboard
    </div>
</div>

<div class="card">
    <h2>Welcome back</h2>
    <p>Sign in to manage the Scholife platform</p>

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            autocomplete="email"
            autofocus
            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
        >
        @error('email')
        <span class="error-msg">{{ $message }}</span>
        @enderror

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
        >
        @error('password')
        <span class="error-msg">{{ $message }}</span>
        @enderror

        <button type="submit" class="btn-signin">Sign In</button>
    </form>
</div>

</body>
</html>
