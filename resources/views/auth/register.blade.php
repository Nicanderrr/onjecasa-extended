<!doctype html>
<html lang="en" data-theme="light">
@php
    $posSettings = \Illuminate\Support\Facades\Schema::hasTable('pos_settings')
        ? \Illuminate\Support\Facades\DB::table('pos_settings')->pluck('value', 'key')->all()
        : [];
    $systemName = \App\Support\BrandAssets::systemName();
    $loginImage = $posSettings['admin_login_image'] ?? null;
    $overlay = max(40, min(85, (int) ($posSettings['admin_login_overlay'] ?? 72)));
    $overlayColor = [
        'red' => '185,28,28', 'blue' => '37,99,235', 'green' => '16,185,129',
        'amber' => '217,119,6', 'slate' => '51,65,85', 'purple' => '124,58,237',
    ][$posSettings['admin_login_overlay_color'] ?? 'red'] ?? '185,28,28';
    $loginImageUrl = $loginImage
        ? asset('assets/admin/img/settings/' . $loginImage)
        : asset('assets/adminhmd/images/png/dasher-ui-bootstrap-5.jpg');
    $logoUrl = \App\Support\BrandAssets::logoUrl();
@endphp
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Create Account - {{ $systemName }}</title>
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/style.css') }}">
  <link rel="icon" href="{{ $logoUrl }}">
  <style>
    html, body { min-height: 100%; }
    body { margin: 0; background: #020617; }
    .boot-overlay { position: fixed; inset: 0; z-index: 10; display: grid; place-items: center; background: #020617; color: #b91c1c; font-size: .85rem; letter-spacing: .2em; text-transform: uppercase; }
    .background-image { position: fixed; inset: 0; background: url('{{ $loginImageUrl }}') center/cover no-repeat; }
    .scanline::after { content: ""; position: fixed; inset: 0; z-index: 2; pointer-events: none; background: repeating-linear-gradient(to bottom, rgba(255,255,255,.02), rgba(255,255,255,.02) 1px, transparent 1px, transparent 4px); opacity: .12; }
    .radar { position: fixed; z-index: 3; width: 500px; height: 500px; border: 1px solid rgba(185,28,28,.2); border-radius: 50%; background: rgba(185,28,28,.05); animation: sweep 4s linear infinite; mix-blend-mode: screen; }
    @keyframes sweep { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .login-container { position: relative; z-index: 5; width: 100%; min-height: 100vh; }
    .glass { min-height: 100vh; overflow: hidden; background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)); backdrop-filter: blur(18px); }
    .auth-layout { display: grid; grid-template-columns: 65% 35%; min-height: 100vh; }
    .auth-splash { position: relative; display: flex; flex-direction: column; justify-content: space-between; min-height: 100vh; padding: 2.5rem; overflow: hidden; color: #fff; background: url('{{ $loginImageUrl }}') center/cover no-repeat; }
    .auth-hue-overlay { position: absolute; inset: 0; z-index: 1; pointer-events: none; background: linear-gradient(160deg, rgba({{ $overlayColor }}, {{ number_format($overlay / 100, 2, '.', '') }}), rgba(2,6,23,.55)); }
    .auth-splash > :not(.auth-hue-overlay) { position: relative; z-index: 2; }
    .auth-badge { width: 58px; height: 58px; display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; background: rgba(255,255,255,.12); color: #fff; }
    .auth-brand { width: 112px; height: 112px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border-radius: 50%; background: rgba(255,255,255,.05); box-shadow: 0 15px 40px rgba(0,0,0,.5); }
    .auth-brand img { width: 64px; height: 64px; object-fit: contain; }
    .auth-title-large { margin: 0 0 10px; color: #fff; font-size: 2.5rem; font-weight: 700; text-transform: uppercase; }
    .auth-subtitle-large { margin-bottom: 30px; color: #fee2e2; font-size: .875rem; letter-spacing: .2em; text-transform: uppercase; opacity: .8; }
    .auth-brand-copy { max-width: 28rem; margin: 0; font-size: 1.1rem; line-height: 1.6; }
    .auth-content { display: flex; align-items: center; padding: 2.4rem; background: var(--admin-surface); }
    .auth-form-wrap { width: min(420px, 100%); margin: 0 auto; }
    .auth-kicker { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .8rem; border-radius: 999px; background: #eef4ff; color: #1d4ed8; font-size: .8rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .auth-title { margin: 1rem 0 .45rem; font-weight: 800; }
    .auth-copy { margin-bottom: 1.4rem; color: var(--admin-muted); line-height: 1.65; }
    .auth-label { margin-bottom: .45rem; font-size: .85rem; font-weight: 700; }
    .input-group-alternative { border: 1px solid var(--admin-border); border-radius: 8px; background: var(--admin-surface-soft); }
    .input-group-alternative .form-control { min-height: 48px; border: 0; background: transparent; }
    .input-group-alternative .input-group-text { border: 0; background: transparent; color: var(--admin-primary); }
    .btn-login { width: 100%; min-height: 46px; border-color: #7f1d1d; border-radius: 8px; background: linear-gradient(to right, #b91c1c, #7f1d1d); font-weight: 800; }
    .btn-login:hover, .btn-login:focus { border-color: #7f1d1d; background: linear-gradient(to right, #7f1d1d, #b91c1c); box-shadow: 0 0 25px rgba(185,28,28,.5); }
    .auth-footer { margin-top: 1.2rem; color: #9ca3af; font-size: .7rem; letter-spacing: .15em; text-align: center; text-transform: uppercase; }
    @media (max-width: 991.98px) { .auth-layout { grid-template-columns: 1fr; } .auth-splash { min-height: 280px; } .auth-content { padding: 2rem; } }
    @media (max-width: 575.98px) { .auth-splash { min-height: 250px; padding: 1.5rem; } .auth-title-large { font-size: 1.8rem; } .auth-brand { width: 78px; height: 78px; } .auth-brand img { width: 48px; height: 48px; } .auth-brand-copy { font-size: .95rem; } .auth-content { padding: 1.5rem; } }
  </style>
</head>
<body class="auth-body scanline">
  <div id="bootOverlay" class="boot-overlay">Initializing Account Interface...</div>
  <div class="background-image"></div>
  <div class="radar"></div>
  <x-shared.mouse-trail />

  <main class="login-container">
    <section class="glass shadow">
      <div class="auth-layout">
        <aside class="auth-splash">
          <div class="auth-hue-overlay"></div>
          <div>
            <div class="auth-badge mb-4"><i class="bi bi-person-plus-fill"></i></div>
            <div class="auth-brand"><img src="{{ $logoUrl }}" alt="{{ $systemName }}"></div>
            <h1 class="auth-title-large">{{ $systemName }}</h1>
            <p class="auth-subtitle-large">Join the store</p>
            <p class="auth-brand-copy">Create your customer account to shop products, track orders, and manage delivery details.</p>
          </div>
          <div class="small text-white-50">Your account keeps your orders and checkout details together.</div>
        </aside>

        <div class="auth-content">
          <form class="auth-form-wrap" method="POST" action="{{ route('register') }}">
            @csrf
            <div class="auth-kicker"><i class="bi bi-person-plus"></i> New Account</div>
            <h2 class="auth-title">Create your {{ $systemName }} account</h2>
            <p class="auth-copy">Sign up in a few seconds and start shopping from the store.</p>

            @if($errors->any())
              <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
              <label class="auth-label" for="name">Full Name</label>
              <div class="input-group input-group-alternative"><span class="input-group-text"><i class="bi bi-person"></i></span><input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required autofocus autocomplete="name"></div>
            </div>
            <div class="mb-3">
              <label class="auth-label" for="email">Email Address</label>
              <div class="input-group input-group-alternative"><span class="input-group-text"><i class="bi bi-envelope"></i></span><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username"></div>
            </div>
            <div class="mb-3">
              <label class="auth-label" for="password">Password</label>
              <div class="input-group input-group-alternative"><span class="input-group-text"><i class="bi bi-lock"></i></span><input id="password" class="form-control" type="password" name="password" placeholder="Create a password" required autocomplete="new-password"></div>
            </div>
            <div class="mb-4">
              <label class="auth-label" for="password_confirmation">Confirm Password</label>
              <div class="input-group input-group-alternative"><span class="input-group-text"><i class="bi bi-shield-check"></i></span><input id="password_confirmation" class="form-control" type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password"></div>
            </div>

            <button class="btn btn-primary btn-login" type="submit">Create Account</button>
            <div class="text-center mt-3" style="font-size: .85rem;"><span class="text-muted">Already have an account?</span> <a href="{{ route('login') }}" style="color:#b91c1c; text-decoration:none; font-weight:700;">Sign in</a></div>
            <div class="auth-footer">Secure customer registration</div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script src="{{ asset('assets/adminhmd/js/bootstrap.bundle.min.js') }}"></script>
  <script>setTimeout(() => { const boot = document.getElementById('bootOverlay'); if (boot) boot.style.display = 'none'; }, 1200);</script>
</body>
</html>
