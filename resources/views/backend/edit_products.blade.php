@extends('backend.admin')

@section('title', 'Edit Website Product - ONJECASA')
@section('page-icon', 'bi bi-pencil-square')
@section('page-eyebrow', 'Storefront Catalog')
@section('page-title', 'Edit Website Product')
@section('page-description', 'Update the public storefront product and keep its linked POS item aligned.')
@section('page-actions')
  <a href="{{ route('view_product') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Website Products
  </a>
@endsection

@section('admin')
@php
  $sizes = [
      'Single Item',
      'Single Bottle',
      '2 Bottles',
      '4 Pack',
      '6 Pack',
      '8 Pack',
      '12 Pack',
      '24 Pack',
      'Small Pack',
      'Family Pack',
      'Value Pack',
      'Carton',
      'Crate',
      'Bundle',
      'Bulk Order',
      'Wholesale Pack',
      '1kg',
      '5kg',
      '10kg',
  ];
  $extraOptions = \App\Support\MealExtras::options(false);
  $storedUnitOptions = $product ? \App\Support\ProductOptions::options($product) : [];
  $storedSizes = collect($storedUnitOptions)->pluck('name')->all();
  $storedOptionPrices = collect($storedUnitOptions)->mapWithKeys(fn ($option) => [$option['name'] => $option['price']])->all();
  $sizes = array_values(array_unique(array_merge($sizes, $storedSizes)));
  $storedColors = $product?->colors ? (is_string($product->colors) ? json_decode($product->colors, true) : $product->colors) : [];
  $storedFeatures = $product?->features ? (is_string($product->features) ? json_decode($product->features, true) : $product->features) : [];
  $selectedSizes = old('sizes', $storedSizes ?: []);
  $optionPrices = old('option_prices', $storedOptionPrices);
  $selectedColors = old('colors', $storedColors ?: []);
  $featureText = old('features.0', implode("\n", $storedFeatures ?: []));
@endphp

<style>
  .website-product-form .option-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: .65rem;
  }

  .website-product-form .option-chip {
    align-items: start;
    gap: .6rem;
  }

  .website-product-form .option-chip-body {
    display: grid;
    gap: .45rem;
    min-width: 0;
    width: 100%;
  }

  .website-product-form .option-chip-price {
    min-height: 38px;
    border-radius: 6px;
    font-size: .85rem;
  }

  .product-basics-layout,
  .product-options-layout {
    display: grid;
    gap: 1rem;
    align-items: stretch;
  }

  .product-basics-layout {
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
    margin-bottom: 1rem;
  }

  .product-options-layout {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .product-basics-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .product-basic-panel,
  .product-option-panel {
    height: 100%;
    padding: 1rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
  }

  .product-basic-panel-full,
  .product-option-panel-full {
    grid-column: 1 / -1;
  }

  .option-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .85rem;
  }

  .option-panel-heading strong,
  .option-panel-heading span {
    display: block;
  }

  .option-panel-heading strong {
    color: var(--admin-text);
    font-size: .92rem;
    line-height: 1.2;
  }

  .option-panel-heading span {
    margin-top: .15rem;
    color: var(--admin-muted);
    font-size: .76rem;
    line-height: 1.35;
  }

  .option-panel-icon {
    width: 34px;
    height: 34px;
    display: inline-grid;
    place-items: center;
    border-radius: 8px;
    background: #eaf2ff;
    color: var(--admin-primary);
    flex: 0 0 auto;
  }

  .website-product-form .option-chip {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-height: 42px;
    padding: .6rem .7rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
    color: var(--admin-text);
    font-size: .82rem;
    font-weight: 700;
  }

  .website-product-form .option-chip input {
    width: 16px;
    height: 16px;
    flex: 0 0 auto;
  }

  .variant-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(96px, 1fr));
    gap: .65rem;
  }

  .variant-option {
    display: grid;
    gap: .35rem;
    justify-items: center;
    padding: .65rem .5rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
    cursor: pointer;
  }

  .variant-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .variant-swatch {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px rgba(15, 23, 42, .14);
  }

  .variant-option:has(input:checked) {
    border-color: #93c5fd;
    background: #eff6ff;
  }

  .variant-option:has(input:checked) .variant-swatch {
    box-shadow: 0 0 0 2px #2563eb;
  }

  .variant-option span {
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 800;
    text-align: center;
  }

  .features-input {
    min-height: 128px;
    resize: vertical;
  }

  .website-image-upload {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1rem;
    border: 2px dashed var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface-soft);
  }

  .website-image-upload img {
    width: 132px;
    height: 132px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--admin-border);
    background: #fff;
  }

  #image, #productVideos { display: none; }

  .gallery-preview-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(104px, 1fr));
    gap: .7rem;
    margin-top: .85rem;
  }

  .gallery-preview-option {
    position: relative;
    display: grid;
    gap: .45rem;
    padding: .55rem;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: #fff;
    transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
  }

  .gallery-preview-option:has(input[type="radio"]:checked) {
    border-color: var(--admin-primary);
    background: #fff8e1;
    box-shadow: 0 0 0 3px rgba(242, 199, 92, .22);
  }

  .gallery-preview-option img,
  .gallery-preview-option video {
    width: 100%;
    aspect-ratio: 1 / 1;
    height: auto;
    border-radius: 8px;
    object-fit: cover;
    background: #111;
  }

  .gallery-preview-option label,
  .gallery-preview-option .thumbnail-radio-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    margin-bottom: 0;
    padding: .42rem .5rem;
    border-radius: 999px;
    background: var(--admin-surface-soft);
    color: var(--admin-muted);
    font-size: .72rem;
    font-weight: 800;
  }

  .gallery-preview-option input[type="radio"],
  .gallery-preview-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    flex: 0 0 auto;
    accent-color: var(--admin-primary);
  }

  .gallery-remove-btn {
    position: absolute;
    top: .45rem;
    right: .45rem;
    z-index: 2;
    width: 30px;
    height: 30px;
    display: inline-grid;
    place-items: center;
    border: 0;
    border-radius: 999px;
    background: rgba(42, 16, 36, .88);
    color: #fff;
    cursor: pointer;
  }

  .media-remove-toggle {
    margin-top: .25rem;
    background: #fff1f2 !important;
    color: #b42318 !important;
  }

  .product-basic-panel .form-group,
  .product-option-panel .form-group {
    margin-bottom: 0;
  }

  .sync-panel {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .85rem .95rem;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: #eff6ff;
  }

  .sync-panel input {
    width: 18px;
    height: 18px;
    margin-top: .2rem;
  }

  .sync-panel strong,
  .sync-panel small {
    display: block;
  }

  .sync-panel strong {
    color: #1e3a8a;
  }

  .sync-panel small {
    color: #475569;
  }

  @media (max-width: 991.98px) {
    .product-basics-layout,
    .product-options-layout {
      grid-template-columns: 1fr;
    }

    .product-basics-fields {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="entity-page website-product-form">
  <section class="entity-hero">
    <div class="entity-hero-copy">
      <p class="eyebrow">Website product #{{ $product->id }}</p>
      <h1>Edit {{ $product->name }}</h1>
      <p>These details control the online storefront. Keep POS sync checked to update the linked in-store item too.</p>
    </div>
    <div class="entity-chip">
      <i class="bi bi-arrow-repeat"></i>
      POS sync is on by default
    </div>
  </section>

  <div class="card shadow entity-card">
    <div class="card-header border-0">
      <h3 class="mb-0">Website product details</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('updateproducts', $product->id) }}" enctype="multipart/form-data" class="compact-form">
        @csrf

        <div class="product-basics-layout">
          <div class="product-basics-fields">
            <div class="product-basic-panel">
              <div class="option-panel-heading">
                <div>
                  <strong>Product Name</strong>
                  <span>The public name customers see on the website.</span>
                </div>
                <span class="option-panel-icon"><i class="bi bi-tag"></i></span>
              </div>
              <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" placeholder="e.g. Premium Rice 5kg" required>
              </div>
            </div>

            <div class="product-basic-panel">
              <div class="option-panel-heading">
                <div>
                  <strong>Website Category</strong>
                  <span>Controls where this item appears online.</span>
                </div>
                <span class="option-panel-icon"><i class="bi bi-grid"></i></span>
              </div>
              <div class="form-group">
                <label>Website Category</label>
                <select name="description" class="form-control" required>
                  <option value="">Select category</option>
                  @foreach($categories ?? [] as $category)
                    <option value="{{ $category->name }}" @selected(old('description', $product->description) === $category->name)>{{ $category->name }}</option>
                  @endforeach
                </select>
                <div class="form-hint">This is the category shoppers use on the website.</div>
              </div>
            </div>

            <div class="product-basic-panel product-basic-panel-full">
              <div class="option-panel-heading">
                <div>
                  <strong>Product Price</strong>
                  <span>The storefront selling price and available quantity for this item.</span>
                </div>
                <span class="option-panel-icon"><i class="bi bi-cash-coin"></i></span>
              </div>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price) }}" placeholder="0.00" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Stock</label>
                    <input type="number" min="0" step="1" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" placeholder="0" required>
                    <div class="form-hint">This quantity syncs to POS when POS sync is checked.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="product-basic-panel">
            <div class="option-panel-heading">
              <div>
                <strong>Product Image</strong>
                <span>Main product photo used on product cards and details.</span>
              </div>
              <span class="option-panel-icon"><i class="bi bi-image"></i></span>
            </div>
            <div class="form-group">
              <label>Product Images</label>
              <div class="website-image-upload">
                <img id="showImage" src="{{ $product->picture_url ?: asset('upload/no_image.jpg') }}" alt="Product preview" onerror="this.onerror=null;this.src='{{ asset('upload/no_image.jpg') }}';">
                <div>
                  <label for="image" class="btn btn-light btn-sm mb-2">
                    <i class="bi bi-upload"></i> Add More Images
                  </label>
                  <input id="image" name="gallery_images[]" type="file" accept="image/*" multiple>
                  <div id="imageFileName" class="form-hint">No new images selected yet</div>
                  <div class="form-hint">Choose an existing or new image as thumbnail. Current thumbnail stays selected by default.</div>
                </div>
                <div class="gallery-preview-grid">
                  @foreach($product->images->where('media_type', 'image') as $image)
                    <div class="gallery-preview-option">
                      <img src="{{ $image->url }}" alt="{{ $product->name }} image" onerror="this.onerror=null;this.src='{{ asset('upload/no_image.jpg') }}';">
                      <label>
                        <input type="radio" name="thumbnail_existing_id" value="{{ $image->id }}" @checked($image->is_thumbnail || $product->picture === $image->path)>
                        Thumbnail
                      </label>
                      <label class="media-remove-toggle">
                        <input type="checkbox" name="remove_media_ids[]" value="{{ $image->id }}">
                        Remove
                      </label>
                    </div>
                  @endforeach
                </div>
                <div id="galleryPreviewGrid" class="gallery-preview-grid"></div>
              </div>
            </div>
          </div>

          <div class="product-basic-panel">
            <div class="option-panel-heading">
              <div>
                <strong>Product Videos</strong>
                <span>Optional short videos shown in the product detail gallery.</span>
              </div>
              <span class="option-panel-icon"><i class="bi bi-camera-video"></i></span>
            </div>
            <div class="form-group">
              <label>Short Product Videos</label>
              <div class="website-image-upload">
                <div>
                  <label for="productVideos" class="btn btn-light btn-sm mb-2">
                    <i class="bi bi-upload"></i> Add Videos
                  </label>
                  <input id="productVideos" name="product_videos[]" type="file" accept="video/mp4,video/webm,video/quicktime,video/x-m4v" multiple>
                  <div id="videoFileName" class="form-hint">No new videos selected yet</div>
                  <div class="form-hint">MP4, WEBM, MOV, or M4V. Max 50MB each. Existing videos stay unless the product is deleted.</div>
                </div>
                <div class="gallery-preview-grid">
                  @foreach($product->images->where('media_type', 'video') as $video)
                    <div class="gallery-preview-option">
                      <video src="{{ $video->url }}" controls muted playsinline style="width:100%;height:92px;object-fit:cover;border-radius:6px;background:#111"></video>
                      <label>
                        <input type="radio" name="thumbnail_existing_id" value="{{ $video->id }}" @checked($video->is_thumbnail)>
                        Thumbnail
                      </label>
                      <label class="media-remove-toggle">
                        <input type="checkbox" name="remove_media_ids[]" value="{{ $video->id }}">
                        Remove
                      </label>
                    </div>
                  @endforeach
                </div>
                <div id="videoPreviewGrid" class="gallery-preview-grid"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Website Product Description</label>
          <textarea rows="4" name="contents" class="form-control" placeholder="Describe the product, option, add-on, availability, and delivery details." required>{{ old('contents', $product->contents) }}</textarea>
        </div>

        <div class="product-options-layout mb-3">
          <div class="product-option-panel">
            <div class="option-panel-heading">
              <div>
                <strong>Product Options</strong>
                <span>Choose how customers can buy this product online.</span>
              </div>
              <span class="option-panel-icon"><i class="bi bi-basket2"></i></span>
            </div>
            <div class="option-grid">
              @foreach($sizes as $size)
                <label class="option-chip">
                  <input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size, $selectedSizes, true))>
                  <span class="option-chip-body">
                    <span>{{ $size }}</span>
                    <input type="number" step="0.01" min="0" name="option_prices[{{ $size }}]" class="form-control option-chip-price" value="{{ $optionPrices[$size] ?? '' }}" placeholder="Option price">
                  </span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="product-option-panel">
            <div class="option-panel-heading">
              <div>
                <strong>Add-ons & Basket Items</strong>
                <span>Select checkout add-ons customers can recognize.</span>
              </div>
              <span class="option-panel-icon"><i class="bi bi-basket"></i></span>
            </div>
            <div class="variant-grid">
              @foreach($extraOptions as $extraOption)
                <label class="variant-option">
                  <input type="checkbox" name="colors[]" value="{{ $extraOption['name'] }}" @checked(in_array($extraOption['name'], $selectedColors, true))>
                  <span class="variant-swatch" style="background-image: url('{{ $extraOption['image_url'] }}'); background-size: cover; background-position: center;">
                    @if(empty($extraOption['image_url']))
                      <i class="bi {{ $extraOption['icon'] }}"></i>
                    @endif
                  </span>
                  <span>{{ $extraOption['name'] }} - GHC {{ number_format($extraOption['price'], 2) }}</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="product-option-panel product-option-panel-full">
            <div class="option-panel-heading">
              <div>
                <strong>Key Features</strong>
                <span>Enter one feature per line. These appear as bullets on the product detail page.</span>
              </div>
              <span class="option-panel-icon"><i class="bi bi-stars"></i></span>
            </div>
            <textarea rows="5" name="features[]" class="form-control features-input" placeholder="Shelf ready&#10;Delivery available&#10;Everyday value&#10;Add-on available">{{ $featureText }}</textarea>
          </div>
        </div>

        <div class="sync-panel mb-3">
          <input type="checkbox" name="sync_to_pos" value="1" checked>
          <div>
            <strong>Also update this product in POS</strong>
            <small>Auto-checked. When saved, the linked POS product is updated for in-store selling.</small>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check2-circle"></i> Save Changes
          </button>
          <a href="{{ route('view_product') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var selectedImages = [];
  var selectedVideos = [];

  document.querySelectorAll('input[name="thumbnail_existing_id"]').forEach(function(input) {
    input.addEventListener('change', function() {
      document.querySelectorAll('input[name="thumbnail_new_index"], input[name="thumbnail_video_index"]').forEach(function(newThumb) { newThumb.checked = false; });
      var media = input.closest('.gallery-preview-option')?.querySelector('img, video');
      if (media && media.tagName === 'IMG') document.getElementById('showImage').src = media.src;
    });
  });

  document.getElementById('image')?.addEventListener('change', function(event) {
    selectedImages = Array.prototype.slice.call(event.target.files || []);
    renderImagePreviews();
  });

  document.getElementById('productVideos')?.addEventListener('change', function(event) {
    selectedVideos = Array.prototype.slice.call(event.target.files || []);
    renderVideoPreviews();
  });

  function renderImagePreviews() {
    var input = document.getElementById('image');
    var previewGrid = document.getElementById('galleryPreviewGrid');
    previewGrid.innerHTML = '';
    document.getElementById('imageFileName').textContent = selectedImages.length ? selectedImages.length + ' new image(s) selected' : 'No new images selected yet';
    syncFileInput(input, selectedImages);

    if (!selectedImages.length) {
      document.getElementById('showImage').src = '{{ $product->picture_url ?: asset('upload/no_image.jpg') }}';
      return;
    }

    selectedImages.forEach(function(selectedFile, index) {
      var reader = new FileReader();
      reader.onload = function(loadEvent) {
        var item = makeThumbnailOption({
          type: 'image',
          src: loadEvent.target.result,
          inputName: 'thumbnail_new_index',
          value: index,
          checked: false,
          label: 'Use as thumbnail'
        });
        addRemoveButton(item, function() {
          selectedImages.splice(index, 1);
          renderImagePreviews();
        });
        item.querySelector('input').addEventListener('change', function() {
          document.querySelectorAll('input[name="thumbnail_existing_id"]').forEach(function(existing) { existing.checked = false; });
          document.querySelectorAll('input[name="thumbnail_video_index"]').forEach(function(videoThumb) { videoThumb.checked = false; });
          document.getElementById('showImage').src = loadEvent.target.result;
        });
        previewGrid.appendChild(item);
      };
      reader.readAsDataURL(selectedFile);
    });
  }

  function renderVideoPreviews() {
    var input = document.getElementById('productVideos');
    var previewGrid = document.getElementById('videoPreviewGrid');
    previewGrid.innerHTML = '';
    document.getElementById('videoFileName').textContent = selectedVideos.length ? selectedVideos.length + ' new video(s) selected' : 'No new videos selected yet';
    syncFileInput(input, selectedVideos);

    selectedVideos.forEach(function(selectedFile, index) {
      var item = makeThumbnailOption({
        type: 'video',
        src: URL.createObjectURL(selectedFile),
        inputName: 'thumbnail_video_index',
        value: index,
        checked: false,
        label: 'Use as thumbnail'
      });
      addRemoveButton(item, function() {
        selectedVideos.splice(index, 1);
        renderVideoPreviews();
      });
      item.querySelector('input').addEventListener('change', function() {
        document.querySelectorAll('input[name="thumbnail_existing_id"], input[name="thumbnail_new_index"]').forEach(function(otherThumb) { otherThumb.checked = false; });
      });
      previewGrid.appendChild(item);
    });
  }

  function makeThumbnailOption(config) {
    var item = document.createElement('label');
    item.className = 'gallery-preview-option';

    var media = document.createElement(config.type === 'video' ? 'video' : 'img');
    media.src = config.src;
    if (config.type === 'video') {
      media.controls = true;
      media.muted = true;
      media.playsInline = true;
    } else {
      media.alt = 'Selected product image';
    }

    var caption = document.createElement('span');
    caption.className = 'thumbnail-radio-label';

    var radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = config.inputName;
    radio.value = config.value;
    radio.checked = Boolean(config.checked);

    caption.appendChild(radio);
    caption.appendChild(document.createTextNode(config.label));
    item.appendChild(media);
    item.appendChild(caption);

    return item;
  }

  function addRemoveButton(item, onRemove) {
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'gallery-remove-btn';
    button.innerHTML = '<i class="bi bi-x-lg"></i>';
    button.setAttribute('aria-label', 'Remove selected media');
    button.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      onRemove();
    });
    item.appendChild(button);
  }

  function syncFileInput(input, files) {
    var transfer = new DataTransfer();
    files.forEach(function(file) {
      transfer.items.add(file);
    });
    input.files = transfer.files;
  }
</script>
@endpush



