@extends('backend.admin')
@section('admin')

<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .form-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
        border: 1px solid #eef2f6;
    }

    .form-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-header h2 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .form-header p {
        color: #64748b;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .form-label i {
        color: #667eea;
        margin-right: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s;
        font-size: 0.95rem;
    }

    .form-control:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #667eea;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        margin-top: 1rem;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -8px rgba(102,126,234,0.6);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 2rem;
        border-radius: 40px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.2s;
        margin-top: 1rem;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
</style>
<br><br><br>

<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h2>Edit FAQ</h2>
            <p>Update the frequently asked question</p>
        </div>

        <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">
                    <i class="ti ti-category"></i>
                    Category
                </label>
                <select name="category" class="form-control" required>
                    <option value="general" {{ $faq->category == 'general' ? 'selected' : '' }}>General Information</option>
                    <option value="services" {{ $faq->category == 'services' ? 'selected' : '' }}>Services</option>
                </select>
                @error('category')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="ti ti-question-mark"></i>
                    Question
                </label>
                <input type="text" name="question" class="form-control" value="{{ old('question', $faq->question) }}" placeholder="Enter the question" required>
                @error('question')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="ti ti-file-text"></i>
                    Answer
                </label>
                <textarea name="answer" class="form-control" placeholder="Enter the answer" required>{{ old('answer', $faq->answer) }}</textarea>
                @error('answer')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="ti ti-sort-ascending"></i>
                    Display Order (Optional)
                </label>
                <input type="number" name="order" class="form-control" value="{{ old('order', $faq->order) }}" min="0">
                <small class="text-muted">Lower numbers appear first</small>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $faq->is_active) ? 'checked' : '' }}>
                    <label for="is_active">Active (visible on website)</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="ti ti-device-floppy me-2"></i>
                Update FAQ
            </button>

            <a href="{{ route('admin.faqs.index') }}" class="btn-cancel w-100 text-center mt-3">
                Cancel
            </a>
        </form>
    </div>
</div>

@endsection