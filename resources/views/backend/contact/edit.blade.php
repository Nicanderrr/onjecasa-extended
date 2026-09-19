@extends('backend.admin')

@section('title', 'Contact Settings - ONJECASA')
@section('page-icon', 'bi bi-telephone')
@section('page-eyebrow', 'Website Admin')
@section('page-title', 'Contact Settings')
@section('page-description', 'Update the public contact page header, phone details, address, and social links.')
@section('page-actions')
  <a href="{{ route('contact_page') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
    <i class="bi bi-box-arrow-up-right"></i> View Contact Page
  </a>
@endsection

@section('admin')
<style>
  .contact-settings-page { display: grid; gap: 1.25rem; min-width: 0; max-width: 100%; }
  .contact-settings-card {
    min-width: 0;
    max-width: 100%;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
    overflow: hidden;
  }
  .contact-settings-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 1.2rem;
    border-bottom: 1px solid var(--admin-border);
  }
  .contact-settings-header h3 { margin: 0; color: var(--admin-text); font-size: 1rem; font-weight: 900; }
  .contact-settings-header p { margin: .25rem 0 0; color: var(--admin-muted); font-size: .85rem; line-height: 1.45; }
  .contact-settings-icon {
    width: 38px;
    height: 38px;
    display: inline-grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: #eaf2ff;
    color: var(--admin-primary);
  }
  .contact-settings-body { padding: 1.2rem; min-width: 0; max-width: 100%; }
  .contact-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; min-width: 0; max-width: 100%; }
  .contact-field label {
    display: block;
    margin-bottom: .4rem;
    color: var(--admin-muted);
    font-size: .74rem;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
  }
  .contact-field { min-width: 0; max-width: 100%; }
  .contact-field .form-control { min-width: 0; max-width: 100%; }
  .contact-field-full { grid-column: 1 / -1; }
  .contact-image-panel {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1rem;
    border: 2px dashed var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
  }
  .contact-image-panel img {
    width: 180px;
    height: 118px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--admin-border);
    background: #fff;
  }
  .contact-help { margin-top: .35rem; color: var(--admin-muted); font-size: .78rem; line-height: 1.45; }
  .contact-actions {
    display: flex;
    justify-content: flex-end;
    gap: .65rem;
    flex-wrap: wrap;
    padding: 1.2rem;
    border-top: 1px solid var(--admin-border);
    background: var(--admin-surface-soft);
  }
  @media (max-width: 767.98px) {
    .contact-grid { grid-template-columns: 1fr; }
    .contact-settings-header { flex-direction: column; }
    .contact-actions .btn { width: 100%; }
  }
</style>

<div class="contact-settings-page">
  @if(session('success'))
    <div class="alert alert-success mb-0">{{ session('success') }}</div>
  @endif

  <form method="POST" action="{{ route('admin.contact.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="contact-settings-card mb-3">
      <div class="contact-settings-header">
        <div>
          <h3>Contact Page Header</h3>
          <p>Controls the top text and image shown on the public contact page.</p>
        </div>
        <span class="contact-settings-icon"><i class="bi bi-image"></i></span>
      </div>
      <div class="contact-settings-body">
        <div class="contact-grid">
          <div class="contact-field">
            <label>Title Line 1</label>
            <input type="text" name="header_title_line1" class="form-control" value="{{ old('header_title_line1', $settings->header_title_line1) }}">
          </div>
          <div class="contact-field">
            <label>Title Line 2</label>
            <input type="text" name="header_title_line2" class="form-control" value="{{ old('header_title_line2', $settings->header_title_line2) }}">
          </div>
          <div class="contact-field contact-field-full">
            <label>Header Image</label>
            <div class="contact-image-panel">
              <img id="showContactHeaderImage" src="{{ $settings->header_image ? asset('storage/' . $settings->header_image) : asset('upload/no_image.jpg') }}" alt="Contact header preview" onerror="this.onerror=null;this.src='{{ asset('upload/no_image.jpg') }}';">
              <div>
                <input type="file" name="header_image" id="contactHeaderImage" class="form-control" accept="image/*">
                <div class="contact-help">Leave empty to keep the current image.</div>
                @error('header_image')<div class="text-danger small fw-bold mt-1">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="contact-settings-card mb-3">
      <div class="contact-settings-header">
        <div>
          <h3>Contact Information</h3>
          <p>Phone, WhatsApp, email, and address details customers use to reach the business.</p>
        </div>
        <span class="contact-settings-icon"><i class="bi bi-person-lines-fill"></i></span>
      </div>
      <div class="contact-settings-body">
        <div class="contact-grid">
          <div class="contact-field">
            <label>Business Number</label>
            <input type="text" name="business_number" class="form-control" value="{{ old('business_number', $settings->business_number) }}">
          </div>
          <div class="contact-field">
            <label>WhatsApp Number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $settings->whatsapp_number) }}">
          </div>
          <div class="contact-field">
            <label>WhatsApp Link</label>
            <input type="url" name="whatsapp_link" class="form-control" value="{{ old('whatsapp_link', $settings->whatsapp_link) }}">
          </div>
          <div class="contact-field">
            <label>Contact Form Email</label>
            <input type="email" name="form_email" class="form-control" value="{{ old('form_email', $settings->form_email) }}">
          </div>
          <div class="contact-field contact-field-full">
            <label>Office Address</label>
            <textarea name="office_address" class="form-control" rows="3">{{ old('office_address', $settings->office_address) }}</textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="contact-settings-card">
      <div class="contact-settings-header">
        <div>
          <h3>Social Links</h3>
          <p>Optional links shown wherever the storefront exposes social contact channels.</p>
        </div>
        <span class="contact-settings-icon"><i class="bi bi-share"></i></span>
      </div>
      <div class="contact-settings-body">
        <div class="contact-grid">
          <div class="contact-field"><label>Facebook</label><input type="url" name="facebook_link" class="form-control" value="{{ old('facebook_link', $settings->facebook_link) }}"></div>
          <div class="contact-field"><label>Instagram</label><input type="url" name="instagram_link" class="form-control" value="{{ old('instagram_link', $settings->instagram_link) }}"></div>
          <div class="contact-field"><label>Twitter/X</label><input type="url" name="twitter_link" class="form-control" value="{{ old('twitter_link', $settings->twitter_link) }}"></div>
          <div class="contact-field"><label>YouTube</label><input type="url" name="youtube_link" class="form-control" value="{{ old('youtube_link', $settings->youtube_link) }}"></div>
          <div class="contact-field"><label>LinkedIn</label><input type="url" name="linkedin_link" class="form-control" value="{{ old('linkedin_link', $settings->linkedin_link) }}"></div>
          <div class="contact-field"><label>Messenger</label><input type="url" name="messenger_link" class="form-control" value="{{ old('messenger_link', $settings->messenger_link) }}"></div>
          <div class="contact-field"><label>Skype</label><input type="url" name="skype_link" class="form-control" value="{{ old('skype_link', $settings->skype_link) }}"></div>
          <div class="contact-field"><label>TikTok</label><input type="url" name="tiktok_link" class="form-control" value="{{ old('tiktok_link', $settings->tiktok_link) }}"></div>
        </div>
      </div>
      <div class="contact-actions">
        <a href="{{ route('contact_page') }}" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-eye"></i> Preview</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Save Contact Settings</button>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('contactHeaderImage')?.addEventListener('change', function(event) {
    var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function(loadEvent) {
      document.getElementById('showContactHeaderImage').src = loadEvent.target.result;
    };
    reader.readAsDataURL(file);
  });
</script>
@endpush


