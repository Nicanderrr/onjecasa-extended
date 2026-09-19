<!doctype html>
<html lang="en" data-theme="light">
@php
    $posSettings = [];
    if (\Illuminate\Support\Facades\Schema::hasTable('pos_settings')) {
        $posSettings = \Illuminate\Support\Facades\DB::table('pos_settings')->pluck('value', 'key')->all();
    }

    $systemName = \App\Support\BrandAssets::systemName();
    $loginImage = $posSettings['admin_login_image'] ?? null;
    $loginOverlay = max(40, min(85, (int) ($posSettings['admin_login_overlay'] ?? 72)));
    $loginOverlayAlpha = number_format($loginOverlay / 100, 2, '.', '');
    $loginOverlayColor = $posSettings['admin_login_overlay_color'] ?? 'red';
    $overlayColors = [
        'red' => '185,28,28',
        'blue' => '37,99,235',
        'green' => '16,185,129',
        'amber' => '217,119,6',
        'slate' => '51,65,85',
        'purple' => '124,58,237',
    ];
    $overlayRgb = $overlayColors[$loginOverlayColor] ?? $overlayColors['red'];
    $loginImageUrl = !empty($loginImage)
        ? asset('assets/admin/img/settings/' . $loginImage)
        : asset('assets/adminhmd/images/png/dasher-ui-bootstrap-5.jpg');
    $logoUrl = \App\Support\BrandAssets::logoUrl();
@endphp
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login - {{ $systemName }}</title>
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/style.css') }}">
  <link rel="icon" href="{{ $logoUrl }}">
  <style>
    html, body {
      height: 100%;
    }

    .boot-overlay {
      position: fixed;
      inset: 0;
      background: #020617;
      color: #b91c1c;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      font-size: 0.85rem;
      letter-spacing: 0.2em;
      text-transform: uppercase;
    }

    .background-image {
      position: absolute;
      inset: 0;
      background: url('{{ asset('assets/adminhmd/images/png/dasher-ui-bootstrap-5.jpg') }}') center center / cover no-repeat;
      z-index: 0;
    }

    .scanline::after {
      content: "";
      position: absolute;
      inset: 0;
      background: repeating-linear-gradient(to bottom, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.02) 1px, transparent 1px, transparent 4px);
      opacity: 0.12;
      pointer-events: none;
      z-index: 2;
    }

    .radar {
      position: absolute;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      border: 1px solid rgba(185, 28, 28, 0.2);
      background: rgba(185, 28, 28, 0.05);
      animation: sweep 4s linear infinite;
      mix-blend-mode: screen;
      z-index: 3;
    }

    @keyframes sweep {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      background: rgba(185, 28, 28, 0.5);
      animation: moveParticle linear infinite;
    }

    @keyframes moveParticle {
      from { transform: translateY(0) translateX(0); opacity: 1; }
      to { transform: translateY(-1000px) translateX(500px); opacity: 0; }
    }

    .login-container {
      position: relative;
      z-index: 5;
      width: 100%;
      min-height: 100vh;
      margin: 0;
      top: 0;
      transform: none;
      padding: 0;
      box-sizing: border-box;
    }

    .glass {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
      backdrop-filter: blur(18px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
      border-radius: 0;
      overflow: hidden;
    }

    .auth-layout {
      display: grid;
      grid-template-columns: 65% 35%;
      min-height: 100vh;
    }

    .auth-splash {
      min-height: 100vh;
      padding: 2.5rem;
      color: #fff;
      background: url('{{ $loginImageUrl }}') center/cover no-repeat;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .auth-hue-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      pointer-events: none;
      background: linear-gradient(160deg, rgba({{ $overlayRgb }}, {{ $loginOverlayAlpha }}), rgba(2, 6, 23, 0.55));
    }

    .auth-splash > :not(.auth-hue-overlay) {
      position: relative;
      z-index: 2;
    }

    .auth-badge {
      width: 58px;
      height: 58px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
    }

    .auth-brand {
      width: 112px;
      height: 112px;
      margin: 0 auto 20px auto;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
      animation: float 6s ease-in-out infinite;
      background: rgba(255, 255, 255, 0.05);
    }

    .auth-brand img {
      width: 64px;
      height: 64px;
      object-fit: contain;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .auth-title-large {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
      margin: 0 0 10px 0;
    }

    .auth-title-large span {
      color: #b91c1c;
    }

    .auth-subtitle-large {
      font-size: 0.875rem;
      color: #fee2e2;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      opacity: 0.8;
      margin-bottom: 30px;
    }

    .auth-content {
      padding: 2.4rem;
      display: flex;
      align-items: center;
      background: var(--admin-surface);
    }

    .auth-form-wrap {
      width: min(420px, 100%);
      margin: 0 auto;
    }

    .auth-kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.45rem 0.8rem;
      border-radius: 999px;
      background: #eef4ff;
      color: #1d4ed8;
      font-size: 0.8rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .auth-title {
      margin: 1rem 0 0.45rem;
      font-weight: 800;
      letter-spacing: 0;
    }

    .auth-copy {
      color: var(--admin-muted);
      margin-bottom: 1.4rem;
      line-height: 1.65;
    }

    .auth-label {
      font-weight: 700;
      font-size: 0.85rem;
      margin-bottom: 0.45rem;
    }

    .input-group-alternative {
      border-radius: 8px;
      border: 1px solid var(--admin-border);
      background: var(--admin-surface-soft);
    }

    .input-group-alternative .form-control {
      border: 0;
      background: transparent;
      min-height: 48px;
    }

    .input-group-alternative .input-group-text {
      border: 0;
      background: transparent;
      color: var(--admin-primary);
    }

    .btn-login {
      width: 100%;
      border-radius: 8px;
      min-height: 46px;
      font-weight: 800;
      background: linear-gradient(to right, #b91c1c, #7f1d1d);
      border-color: #7f1d1d;
    }

    .btn-login:hover,
    .btn-login:focus {
      background: linear-gradient(to right, #7f1d1d, #b91c1c);
      border-color: #7f1d1d;
      box-shadow: 0 0 25px rgba(185, 28, 28, 0.5);
    }

    .auth-footer {
      margin-top: 1.2rem;
      text-align: center;
      font-size: 0.7rem;
      color: #9CA3AF;
      letter-spacing: 0.15em;
      text-transform: uppercase;
    }

    .caps-warning {
      font-size: 0.7rem;
      color: #fca5a5;
      margin-top: 5px;
      display: none;
    }

    .auth-brand-copy {
      max-width: 28rem;
      font-size: 1.1rem;
      line-height: 1.6;
    }

    @media (max-width: 991.98px) {
      .auth-layout {
        grid-template-columns: 1fr;
        min-height: auto;
      }

      .auth-splash {
        min-height: 280px;
      }

      .login-container {
        min-height: 100vh;
      }

      .auth-content {
        padding: 2rem;
      }
    }
  </style>
</head>
<body class="auth-body">
  <div id="bootOverlay" class="boot-overlay">Initializing Command Interface...</div>
  <div class="background-image"></div>
  <div class="scanline"></div>
  <div class="radar"></div>
  <div id="particles"></div>
  <x-shared.mouse-trail />

  <main class="login-container">
    <section class="glass shadow">
      <div class="auth-layout">
        <aside class="auth-splash">
          <div class="auth-hue-overlay"></div>
          <div>
            <div class="auth-badge mb-4"><i class="bi bi-grid-1x2-fill"></i></div>
            <div class="auth-brand">
              <img src="{{ $logoUrl }}" alt="{{ $systemName }}">
            </div>
            <h1 class="auth-title-large">{{ $systemName }}</h1>
            <p class="auth-subtitle-large">Command Center</p>
            <p class="auth-brand-copy mb-0">
              A cleaner workspace for orders, products, payments, cashier sales, and store operations.
            </p>
          </div>
          <div class="small text-white-50">Admins verify with email OTP after password login.</div>
        </aside>

        <div class="auth-content">
          <form id="loginForm" class="auth-form-wrap" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="auth-kicker"><i class="bi bi-shield-lock"></i> Secure Login</div>
            <h2 class="auth-title">Sign in to {{ $systemName }}</h2>
            <p class="auth-copy">Admins use email and password, then OTP. Cashiers can use email, staff number, or username with password.</p>

            @if(session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
              <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
              <label class="auth-label" for="email">Email / Username</label>
              <div class="input-group input-group-alternative">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input id="email" class="form-control" type="text" name="email" value="{{ old('email') }}" placeholder="Email or username" required autofocus autocomplete="username">
              </div>
            </div>

            <div class="mb-3">
              <label class="auth-label" for="passwordField">Password</label>
              <div class="input-group input-group-alternative">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="passwordField" class="form-control" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                <button type="button" id="togglePassword" class="btn btn-link px-2 text-decoration-none" aria-label="Toggle password visibility">
                  <i id="eyeIconSVG" class="bi bi-eye"></i>
                  <i id="eyeOffIconSVG" class="bi bi-eye-slash d-none"></i>
                </button>
              </div>
              <p id="capsWarning" class="caps-warning mb-0">CAPS LOCK ACTIVE</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4" style="font-size: 0.75rem; color: #9CA3AF;">
              <label class="d-flex align-items-center cursor-pointer mb-0">
                <input id="remember_me" type="checkbox" class="me-2" name="remember">
                <span>Remember Station</span>
              </label>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="color:#b91c1c; text-decoration:none; font-weight:600;">HQ Support</a>
              @endif
            </div>

            <button id="loginBtn" class="btn btn-primary btn-login" type="submit">Login</button>

            <div class="text-center mt-3" style="font-size: 0.85rem;">
              <span class="text-muted">New to {{ $systemName }}?</span>
              <a href="{{ route('register') }}" style="color:#b91c1c; text-decoration:none; font-weight:700;">Create an account</a>
            </div>

            <div class="auth-footer">
              Admin OTP screen follows after login.
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script src="{{ asset('assets/adminhmd/js/bootstrap.bundle.min.js') }}"></script>
  <script>
    setTimeout(() => {
      const boot = document.getElementById('bootOverlay');
      if (boot) boot.style.display = 'none';
    }, 1200);

    document.addEventListener('DOMContentLoaded', function () {
      const togglePassword = document.getElementById('togglePassword');
      const password = document.getElementById('passwordField');
      const eyeIcon = document.getElementById('eyeIconSVG');
      const eyeOffIcon = document.getElementById('eyeOffIconSVG');
      const capsWarning = document.getElementById('capsWarning');

      if (togglePassword && password) {
        togglePassword.addEventListener('click', function () {
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          eyeIcon.classList.toggle('d-none', type === 'password');
          eyeOffIcon.classList.toggle('d-none', type !== 'password');
        });
      }

      if (password && capsWarning) {
        password.addEventListener('keyup', function (event) {
          capsWarning.style.display = event.getModifierState('CapsLock') ? 'block' : 'none';
        });
      }

      const loginForm = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginBtn');
      if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', () => {
          loginBtn.innerText = 'Verifying Credentials...';
        });
      }

      const particlesContainer = document.getElementById('particles');
      if (particlesContainer) {
        for (let i = 0; i < 80; i++) {
          const p = document.createElement('div');
          p.className = 'particle';
          p.style.top = Math.random() * window.innerHeight + 'px';
          p.style.left = Math.random() * window.innerWidth + 'px';
          p.style.width = p.style.height = (Math.random() * 3 + 1) + 'px';
          p.style.animationDuration = (Math.random() * 5 + 5) + 's';
          particlesContainer.appendChild(p);
        }
      }
    });
  </script>
</body>
</html>

