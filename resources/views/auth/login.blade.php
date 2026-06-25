<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Hisab-Kitab ERP</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at top, var(--bg-sidebar), var(--bg-primary));
        }
        
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-lg), var(--shadow-glow);
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-info));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .login-title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .login-btn {
            width: 100%;
            padding: 0.85rem;
            font-size: 1rem;
            margin-top: 1rem;
            border-radius: 12px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-logo">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <h1 class="login-title">Hisab-Kitab</h1>
        <p class="login-subtitle">Enter your mobile number to access the panel</p>

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--color-danger); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem; text-align: left;">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem;"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--color-success); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem; text-align: left;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group" style="text-align: left;">
                <label for="mobile" style="font-weight: 600;">Mobile Number</label>
                <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="e.g. 9106798459" required autofocus autocomplete="tel" style="padding: 0.85rem; font-size: 1.05rem; letter-spacing: 1px;">
            </div>
            
            <button type="submit" class="btn btn-primary login-btn">
                Secure Login <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>
        </form>
    </div>

</body>
</html>
