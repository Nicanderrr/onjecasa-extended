<!doctype html>
<html lang="en">
@php
  $globalLogo = \Illuminate\Support\Facades\DB::table('pos_settings')->where('key', 'sidebar_logo')->value('value');
  $globalLogoUrl = $globalLogo
    ? asset('assets/admin/img/settings/' . $globalLogo)
    : asset('assets/adminhmd/images/brand/logo/logo-icon.svg');
  $systemName = \Illuminate\Support\Facades\DB::table('pos_settings')->where('key', 'system_name')->value('value') ?? 'ONJECASA';
@endphp
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance | {{ $systemName }}</title>
  <link rel="icon" href="{{ $globalLogoUrl }}">
  <style>
    :root {
      --food: #2f9b55;
      --food-deep: #2a0617;
      --gold: #f4b63f;
      --paper: #fffaf0;
      --ink: #27131d;
    }

    * { box-sizing: border-box; }

    body {
      align-items: center;
      background:
        radial-gradient(circle at 20% 10%, rgba(242, 199, 92, .28), transparent 30%),
        linear-gradient(135deg, #fff6f7, var(--paper));
      color: var(--ink);
      display: flex;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      justify-content: center;
      margin: 0;
      min-height: 100vh;
      padding: 24px;
    }

    .maintenance-card {
      background: rgba(255, 255, 255, .94);
      border: 1px solid rgba(111, 29, 53, .16);
      border-radius: 24px;
      box-shadow: 0 30px 90px rgba(42, 6, 23, .16);
      max-width: 640px;
      padding: 42px;
      text-align: center;
      width: 100%;
    }

    .logo {
      border: 3px solid rgba(242, 199, 92, .5);
      border-radius: 999px;
      display: inline-grid;
      height: 86px;
      margin-bottom: 22px;
      overflow: hidden;
      place-items: center;
      width: 86px;
    }

    .logo img {
      height: 100%;
      object-fit: cover;
      width: 100%;
    }

    .pill {
      background: rgba(111, 29, 53, .1);
      border-radius: 999px;
      color: var(--food);
      display: inline-flex;
      font-size: 12px;
      font-weight: 900;
      letter-spacing: .16em;
      padding: 9px 14px;
      text-transform: uppercase;
    }

    h1 {
      color: var(--food-deep);
      font-family: Constantia, Georgia, serif;
      font-size: clamp(34px, 7vw, 58px);
      line-height: 1;
      margin: 22px 0 14px;
    }

    p {
      color: #715a64;
      font-size: 16px;
      line-height: 1.7;
      margin: 0;
    }

    .brand {
      color: var(--gold);
      display: inline-block;
      font-weight: 900;
      margin-top: 22px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <main class="maintenance-card">
    <span class="logo"><img src="{{ $globalLogoUrl }}" alt="{{ $systemName }}"></span>
    <br>
    <span class="pill">Maintenance</span>
    <h1>We will be right back.</h1>
    <p>{{ $note ?: $systemName . ' is getting a quick system update. Please check back shortly.' }}</p>
    <span class="brand">{{ $systemName }}</span>
  </main>
</body>
</html>

