@extends('layouts.pos-admin')

@section('title', 'Settings - ONJECASA POS')
@section('page-icon', 'bi bi-sliders')
@section('page-eyebrow', 'Workspace')
@section('page-title', 'Settings')
@section('page-description', 'Tune security, branding, and visual presets from one control center.')
@section('page-actions')
  <button type="submit" form="settings-form" class="btn btn-primary btn-sm">
    <i class="bi bi-check2-circle"></i> Save Changes
  </button>
@endsection

@section('content')
@php
  $loginPreview = !empty($loginImage)
    ? asset('assets/admin/img/settings/' . $loginImage)
    : asset('assets/adminhmd/images/png/dasher-ui-bootstrap-5.jpg');
  $pinPreview = !empty($pinImage)
    ? asset('assets/admin/img/settings/' . $pinImage)
    : asset('assets/adminhmd/images/png/dasher-ai.png');
  $logoPreview = !empty($sidebarLogo)
    ? asset('assets/admin/img/settings/' . $sidebarLogo)
    : \App\Support\BrandAssets::logoUrl();
@endphp

<div class="settings-page">
  <div class="settings-grid">
    <div class="card shadow settings-card">
      <div class="card-header border-0">
        <div class="settings-section-title">
          <i class="bi bi-gear"></i>
          Core Settings
        </div>
        <p class="settings-section-copy mb-0">Update profile access, appearance defaults, and global branding from one form.</p>
      </div>

      <div class="card-body">
        <form id="settings-form" method="POST" action="{{ route('pos.admin.settings.update') }}" enctype="multipart/form-data" class="settings-section">
          @csrf
          @method('PUT')

          <div class="settings-group">
            <h3>Identity & Security</h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" value="{{ auth()->user()->name }}" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ auth()->user()->email }}" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep the current password">
                <div class="settings-help">Only set this if you want to change the admin login password.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">New Pincode</label>
                <input type="password" name="pincode" class="form-control" placeholder="Leave blank to keep the current PIN">
                <div class="settings-help">This controls the admin PIN screen after login.</div>
              </div>
            </div>
          </div>

          <div class="settings-group">
            <h3>Branding & Appearance</h3>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Login Image</label>
                <input type="file" name="login_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <div class="settings-help">This image appears on the shared system login screen. Recommended wide image. Max 4MB.</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Current Login Image</label>
                <div class="settings-image-frame">
                  <img src="{{ $loginPreview }}" alt="Current login image">
                </div>
              </div>
              <div class="col-md-8">
                <label class="form-label">Login Image Dark Overlay (40 - 85)</label>
                <input type="range" min="40" max="85" name="login_overlay" value="{{ $loginOverlay ?? 72 }}" class="form-range" id="login_overlay_range">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                  <input type="number" min="40" max="85" id="login_overlay_input" value="{{ $loginOverlay ?? 72 }}" class="form-control" style="max-width:120px;">
                  <span class="settings-help mb-0">Higher values create a darker overlay.</span>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Login Hue Color</label>
                <select name="login_overlay_color" id="login_overlay_color" class="form-select">
                  <option value="black" {{ ($loginOverlayColor ?? 'slate') === 'black' ? 'selected' : '' }}>Black</option>
                  <option value="slate" {{ ($loginOverlayColor ?? 'slate') === 'slate' ? 'selected' : '' }}>Slate</option>
                  <option value="blue" {{ ($loginOverlayColor ?? 'slate') === 'blue' ? 'selected' : '' }}>Blue</option>
                  <option value="amber" {{ ($loginOverlayColor ?? 'slate') === 'amber' ? 'selected' : '' }}>Amber</option>
                  <option value="red" {{ ($loginOverlayColor ?? 'slate') === 'red' ? 'selected' : '' }}>Red</option>
                </select>
                <div id="login_overlay_preview" class="mt-2 d-flex align-items-center gap-2 rounded-3 px-3 py-2 border" style="min-height: 44px;">
                  <span class="rounded-circle border" style="width:14px;height:14px;background:#334155;display:inline-block;"></span>
                  <div class="small lh-sm">
                    <div class="fw-semibold">Live preview</div>
                    <div class="text-muted" id="login_overlay_preview_text">Slate tint at 72%</div>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <label class="form-label">PIN Side Image</label>
                <input type="file" name="pin_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <div class="settings-help">This image appears on the admin PIN screen. Max 4MB.</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Current PIN Image</label>
                <div class="settings-image-frame">
                  <img src="{{ $pinPreview }}" alt="Current PIN image">
                </div>
              </div>
              <div class="col-md-8">
                <label class="form-label">PIN Side Dark Overlay (40 - 85)</label>
                <input type="range" min="40" max="85" name="pin_overlay" value="{{ $pinOverlay ?? 72 }}" class="form-range" id="pin_overlay_range">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                  <input type="number" min="40" max="85" id="pin_overlay_input" value="{{ $pinOverlay ?? 72 }}" class="form-control" style="max-width:120px;">
                  <span class="settings-help mb-0">Higher values create a darker PIN screen overlay.</span>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">PIN Hue Color</label>
                <select name="pin_overlay_color" id="pin_overlay_color" class="form-select">
                  <option value="black" {{ ($pinOverlayColor ?? 'slate') === 'black' ? 'selected' : '' }}>Black</option>
                  <option value="slate" {{ ($pinOverlayColor ?? 'slate') === 'slate' ? 'selected' : '' }}>Slate</option>
                  <option value="blue" {{ ($pinOverlayColor ?? 'slate') === 'blue' ? 'selected' : '' }}>Blue</option>
                  <option value="amber" {{ ($pinOverlayColor ?? 'slate') === 'amber' ? 'selected' : '' }}>Amber</option>
                  <option value="red" {{ ($pinOverlayColor ?? 'slate') === 'red' ? 'selected' : '' }}>Red</option>
                </select>
                <div id="pin_overlay_preview" class="mt-2 d-flex align-items-center gap-2 rounded-3 px-3 py-2 border" style="min-height: 44px;">
                  <span class="rounded-circle border" style="width:14px;height:14px;background:#334155;display:inline-block;"></span>
                  <div class="small lh-sm">
                    <div class="fw-semibold">Live preview</div>
                    <div class="text-muted" id="pin_overlay_preview_text">Slate tint at 72%</div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Dark Mode</label>
                <div class="settings-toggle-row">
                  <div class="settings-switch">
                    <div class="form-check form-switch mb-0">
                      <input type="checkbox" class="form-check-input" id="dark_mode" name="dark_mode" value="1" {{ !empty($darkMode) ? 'checked' : '' }}>
                    </div>
                    <label class="form-check-label mb-0" for="dark_mode">Enable admin dark theme</label>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Mouse Trail</label>
                <div class="settings-toggle-row">
                  <div class="settings-switch">
                    <div class="form-check form-switch mb-0">
                      <input type="checkbox" class="form-check-input" id="global_mouse_trail" name="global_mouse_trail" value="1" {{ !empty($mouseTrailEnabled) ? 'checked' : '' }}>
                    </div>
                    <label class="form-check-label mb-0" for="global_mouse_trail">Enable cursor trail effect</label>
                  </div>
                </div>
                <div class="settings-help">Turn this off if you want a cleaner interface without the animated cursor effect.</div>
              </div>
            </div>
          </div>

          <div class="settings-group">
            <h3>Global Branding</h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">System Name</label>
                <input type="text" name="system_name" class="form-control" maxlength="30" value="{{ $systemName ?? 'POS' }}" placeholder="e.g. ONJECASA POS">
                <div class="settings-help">This name appears in POS, admin, website, and login brand areas.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Global Logo & Favicon</label>
                <input type="file" name="sidebar_logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                <div class="settings-help">Square logo works best. Used across POS, cashier, website header/footer, login screens, and browser favicon. Max 2MB.</div>
                <div class="mt-3 d-flex align-items-center gap-3 flex-wrap">
                  <img src="{{ $logoPreview }}" alt="Current global logo" class="settings-mini-image">
                  <div>
                    <div class="settings-preview-label">Current Logo</div>
                    <div class="settings-preview-value">{{ $systemName ?? 'POS' }}</div>
                    <p class="settings-preview-note mb-0">Used as the brand logo and favicon across the system.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
  (function () {
    const range = document.getElementById('login_overlay_range');
    const input = document.getElementById('login_overlay_input');
    const pinRange = document.getElementById('pin_overlay_range');
    const pinInput = document.getElementById('pin_overlay_input');
    const loginColor = document.getElementById('login_overlay_color');
    const pinColor = document.getElementById('pin_overlay_color');
    const loginPreview = document.getElementById('login_overlay_preview');
    const pinPreview = document.getElementById('pin_overlay_preview');
    const loginPreviewText = document.getElementById('login_overlay_preview_text');
    const pinPreviewText = document.getElementById('pin_overlay_preview_text');

    const colorMap = {
      black: { rgb: '0,0,0', label: 'Black' },
      slate: { rgb: '51,65,85', label: 'Slate' },
      blue: { rgb: '37,99,235', label: 'Blue' },
      amber: { rgb: '217,119,6', label: 'Amber' },
      red: { rgb: '185,28,28', label: 'Red' },
    };

    const clampStrength = (value) => {
        let v = parseInt(value || '72', 10);
        if (Number.isNaN(v)) v = 72;
        return Math.max(40, Math.min(85, v));
    };

    const syncPair = (rangeControl, numberControl) => {
      if (!rangeControl || !numberControl) return;
      rangeControl.addEventListener('input', () => {
        numberControl.value = clampStrength(rangeControl.value);
      });
      numberControl.addEventListener('input', () => {
        rangeControl.value = clampStrength(numberControl.value);
      });
      const initialValue = clampStrength(rangeControl.value);
      rangeControl.value = initialValue;
      numberControl.value = initialValue;
    };

    const syncPreview = (colorSelect, strengthControls, previewEl, previewTextEl) => {
      if (!colorSelect || !strengthControls.length || !previewEl || !previewTextEl) return;
      const update = () => {
        const key = colorSelect.value || 'slate';
        const item = colorMap[key] || colorMap.slate;
        const strength = clampStrength(strengthControls[0].value);
        const alpha = Math.max(0.4, Math.min(0.85, strength / 100));
        previewEl.style.background = `linear-gradient(135deg, rgba(${item.rgb}, ${alpha}), rgba(15, 23, 42, 0.08))`;
        previewEl.style.borderColor = `rgba(${item.rgb}, 0.28)`;
        previewTextEl.textContent = `${item.label} tint at ${strength}%`;
        const dot = previewEl.querySelector('span');
        if (dot) dot.style.background = `rgb(${item.rgb})`;
      };
      colorSelect.addEventListener('change', update);
      strengthControls.forEach((control) => control.addEventListener('input', update));
      update();
    };

    syncPair(range, input);
    syncPair(pinRange, pinInput);
    syncPreview(loginColor, [range, input], loginPreview, loginPreviewText);
    syncPreview(pinColor, [pinRange, pinInput], pinPreview, pinPreviewText);
  })();
</script>
@endsection


