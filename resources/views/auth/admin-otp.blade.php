<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Admin OTP - {{ $systemName }}</title>
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/adminhmd/css/style.css') }}">
  <link rel="icon" href="{{ $favicon }}">
  <style>
    html, body {
      height: 100%;
    }

    .boot-overlay {
      position: fixed;
      inset: 0;
      background: #020617;
      color: #166534;
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
      background: url('{{ asset('assets/adminhmd/images/png/dasher-ai.png') }}') center center / cover no-repeat;
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
      border: 1px solid rgba(16, 185, 129, 0.2);
      background: rgba(16, 185, 129, 0.05);
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
      background: rgba(16, 185, 129, 0.45);
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

    .pin-splash {
      min-height: 100vh;
      padding: 2.4rem;
      color: #fff;
      background: url('{{ $splashMedia['url'] }}') center/cover no-repeat;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .auth-media-video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }

    .auth-hue-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      pointer-events: none;
    }

    .pin-splash > :not(.auth-media-video):not(.auth-hue-overlay) {
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
      color: #10b981;
    }

    .auth-subtitle-large {
      font-size: 0.875rem;
      color: #d1fae5;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      opacity: 0.82;
      margin-bottom: 30px;
    }

    .auth-content {
      padding: 2.4rem;
      display: flex;
      align-items: center;
      background: var(--admin-surface);
    }

    html[data-theme="dark"] .auth-content {
      background: var(--admin-surface);
    }

    .pin-form-wrap {
      width: min(390px, 100%);
      margin: 0 auto;
    }

    .pin-kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.45rem 0.8rem;
      border-radius: 999px;
      background: #dcfce7;
      color: #166534;
      font-size: 0.8rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .pin-title {
      margin: 1rem 0 0.45rem;
      font-weight: 800;
      letter-spacing: -0.04em;
    }

    .pin-copy {
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

    .btn-otp {
      width: 100%;
      border-radius: 8px;
      min-height: 46px;
      font-weight: 800;
      background: linear-gradient(to right, #0f766e, #065f46);
      border-color: #0f766e;
    }

    .btn-otp:hover,
    .btn-otp:focus {
      background: linear-gradient(to right, #065f46, #0f766e);
      border-color: #065f46;
      box-shadow: 0 0 25px rgba(15, 118, 129, 0.4);
    }

    .otp-test-code {
      margin-bottom: 1rem;
      border: 1px dashed #10b981;
      border-radius: 8px;
      background: #ecfdf5;
      color: #065f46;
      padding: 0.85rem 1rem;
      font-size: 0.85rem;
      line-height: 1.45;
    }

    .otp-test-code strong {
      display: block;
      font-size: 1.35rem;
      letter-spacing: 0.2em;
      margin-top: 0.2rem;
    }

    .pin-greeting {
      margin-top: 0.75rem;
      color: var(--admin-muted);
      font-size: 0.9rem;
    }

    .auth-footer {
      margin-top: 1.2rem;
      text-align: center;
      font-size: 0.7rem;
      color: #9CA3AF;
      letter-spacing: 0.15em;
      text-transform: uppercase;
    }

    @media (max-width: 991.98px) {
      .auth-layout {
        grid-template-columns: 1fr;
        min-height: auto;
      }

      .pin-splash {
        min-height: 240px;
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
        <aside class="pin-splash">
          @if(!empty($splashMedia['isVideo']))
            <video class="auth-media-video" src="{{ $splashMedia['url'] }}" autoplay muted loop playsinline></video>
          @endif
          <div class="auth-hue-overlay" style="{{ $overlayStyle ?? 'background: linear-gradient(160deg, rgba(16, 185, 129, 0.72), rgba(2, 6, 23, 0.55));' }}"></div>
          <div>
            <div class="auth-badge mb-4"><i class="bi bi-lock-fill"></i></div>
            <div class="auth-brand">
              <img src="{{ $brandLogo }}" alt="{{ $systemName }}">
            </div>
            <h1 class="auth-title-large">Admin <span>OTP</span></h1>
            <p class="auth-subtitle-large">Verification Layer</p>
            <p class="mb-0" style="max-width: 28rem; font-size: 1.1rem; line-height: 1.6;">
              {{ $adminName }}, enter the email code to complete access to the admin workspace.
            </p>
          </div>
          <div class="small text-white-50">A one-time code is required for admin accounts.</div>
        </aside>

        <div class="auth-content">
          <form id="otpForm" class="pin-form-wrap" method="POST" action="{{ route('admin.otp.verify') }}">
            @csrf
            <div class="pin-kicker"><i class="bi bi-shield-check"></i> Verification</div>
            <h2 class="pin-title">Enter your OTP</h2>
            <p class="pin-copy">We sent a 6-digit code to {{ $adminEmail }}.</p>

            @if($errors->any())
              <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(!empty($otpMailNotice))
              <div class="alert alert-warning">{{ $otpMailNotice }}</div>
            @endif

            @if(!empty($testOtp))
              <div class="otp-test-code">
                Testing code shown on page
                <strong>{{ $testOtp }}</strong>
                <span>Expires at {{ $otpExpiresAt }}.</span>
              </div>
            @endif

            <div class="mb-3">
              <label class="auth-label" for="admin_otp">Email OTP</label>
              <div class="input-group input-group-alternative">
                <span class="input-group-text"><i class="bi bi-envelope-check"></i></span>
                <input id="admin_otp" class="form-control" type="text" name="admin_otp" maxlength="6" minlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="Enter OTP" required autofocus>
              </div>
            </div>

            <button class="btn btn-primary btn-otp" type="submit">Enter Dashboard</button>
            <div class="pin-greeting">Hello, <strong>{{ $adminName }}</strong>.</div>
            <div class="auth-footer">Admin OTP screen follows after login.</div>
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
      const otp = document.getElementById('admin_otp');
      if (otp) {
        otp.focus();
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
