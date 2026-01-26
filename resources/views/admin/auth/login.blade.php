<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dometopia Super Admin | Login</title>

    <!-- Google Font: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style: AdminLTE (Base) -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(148, 163, 184, 0.1);
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-box {
            width: 400px;
            background: transparent;
        }

        .login-logo a {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: #fff !important;
            font-size: 2.5rem;
            letter-spacing: -0.5px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 16px;
            overflow: hidden;
        }

        .card-body {
            padding: 3rem 2rem;
            color: var(--text-color);
        }

        .login-box-msg {
            color: var(--text-muted);
            font-weight: 400;
            margin-bottom: 2rem;
        }

        .form-control {
            background-color: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-color);
            color: white;
            height: 50px;
            padding-left: 1.25rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .form-control::placeholder {
            color: #475569;
        }

        .input-group-text {
            background-color: transparent;
            border: 1px solid var(--border-color);
            border-left: none;
            color: var(--text-muted);
            border-radius: 0 0.5rem 0.5rem 0;
        }
        
        .input-group .form-control {
            border-right: none;
        }
        
        /* Fix overlapping borders */
        .input-group-append .input-group-text {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            height: 50px;
            border-radius: 0.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }

        .icheck-primary label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .auth-links p {
            color: #ef4444; /* Red for errors */
            background: rgba(239, 68, 68, 0.1);
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        /* Input Icon Fix */
        .input-group {
            position: relative;
        }
        .form-control {
            border-right: 1px solid var(--border-color) !important;
            border-radius: 0.5rem !important;
        }
        .input-group-append {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            z-index: 4;
        }
        .input-group-text {
            border: none;
            background: transparent;
            height: 100%;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-box {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body class="hold-transition">
<div class="login-box">
    <div class="login-logo mb-4">
        <a href="#">DOMETOPIA</a>
        <div style="font-size: 1rem; color: #94a3b8; font-family: 'Inter', sans-serif; font-weight: 400; letter-spacing: 2px; text-transform: uppercase;">
            Super Administration
        </div>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Authentication Required</p>

            <form action="{{ route('admin.login') }}" method="post">
                @csrf
                <div class="form-group mb-4">
                    <input type="text" name="manager_id" class="form-control" placeholder="Admin ID" value="{{ old('manager_id') }}" required>
                </div>
                <div class="form-group mb-4">
                    <input type="password" name="mpasswd" class="form-control" placeholder="Secure Password" required>
                </div>
                
                <div class="row align-items-center mb-4">
                    <div class="col-6">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">ACCESS DASHBOARD</button>
                    </div>
                </div>
            </form>

            @if($errors->any())
                <div class="auth-links text-center mt-4">
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
