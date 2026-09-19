<style>
  .barcode-input-action {
    display: flex;
    gap: .5rem;
    align-items: stretch;
  }

  .barcode-input-action .form-control {
    min-width: 0;
  }

  .barcode-camera-modal {
    position: fixed;
    inset: 0;
    z-index: 1080;
    display: none;
    padding: 1rem;
    background: rgba(15, 23, 42, .68);
  }

  .barcode-camera-modal.is-open {
    display: grid;
    place-items: center;
  }

  .barcode-camera-dialog {
    width: min(420px, 100%);
    overflow: hidden;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: var(--admin-surface);
    box-shadow: var(--admin-shadow-lg);
  }

  .barcode-camera-header,
  .barcode-camera-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem;
    border-bottom: 1px solid var(--admin-border);
    background: var(--admin-surface-soft);
  }

  .barcode-camera-footer {
    border-top: 1px solid var(--admin-border);
    border-bottom: 0;
  }

  .barcode-camera-title {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-width: 0;
  }

  .barcode-camera-title span {
    width: 32px;
    height: 32px;
    display: inline-grid;
    place-items: center;
    border-radius: 8px;
    background: rgba(37, 99, 235, .12);
    color: var(--admin-primary);
    flex: 0 0 auto;
  }

  .barcode-camera-title strong,
  .barcode-camera-title small {
    display: block;
  }

  .barcode-camera-title strong {
    color: var(--admin-text);
    line-height: 1.2;
  }

  .barcode-camera-title small,
  .barcode-camera-status {
    color: var(--admin-muted);
    font-size: .82rem;
    line-height: 1.35;
  }

  .barcode-camera-body {
    padding: .75rem;
  }

  .barcode-video-wrap {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    background: #020617;
  }

  .barcode-video-wrap video {
    width: 100%;
    aspect-ratio: 4 / 3;
    display: block;
    min-height: 260px;
    object-fit: contain;
  }

  .barcode-fallback-reader {
    display: none;
    min-height: 220px;
    background: #020617;
  }

  .barcode-scan-frame {
    position: absolute;
    inset: 34% 8%;
    border: 2px solid #22c55e;
    border-radius: 8px;
    box-shadow: 0 0 0 999px rgba(2, 6, 23, .34);
    pointer-events: none;
  }

  .barcode-camera-status.is-success {
    color: #166534;
  }

  .barcode-camera-status.is-error {
    color: #b42318;
  }

  @media (max-width: 575.98px) {
    .barcode-input-action {
      display: grid;
    }

    .barcode-camera-header,
    .barcode-camera-footer {
      align-items: flex-start;
      flex-direction: column;
    }
  }
</style>

<div class="barcode-camera-modal" id="barcodeCameraModal" aria-hidden="true">
  <div class="barcode-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="barcodeCameraTitle">
    <div class="barcode-camera-header">
      <div class="barcode-camera-title">
        <span><i class="bi bi-camera-video" aria-hidden="true"></i></span>
        <div>
          <strong id="barcodeCameraTitle">Scan Product Barcode</strong>
          <small>Point the camera at the barcode until it is detected.</small>
        </div>
      </div>
      <button type="button" class="btn btn-outline-secondary btn-sm" data-barcode-close>
        <i class="bi bi-x-lg" aria-hidden="true"></i>
        Close
      </button>
    </div>
    <div class="barcode-camera-body">
      <div class="barcode-video-wrap">
        <video id="barcodeCameraVideo" muted playsinline></video>
        <div class="barcode-fallback-reader" id="barcodeFallbackReader"></div>
        <div class="barcode-scan-frame" aria-hidden="true"></div>
      </div>
    </div>
    <div class="barcode-camera-footer">
      <div class="barcode-camera-status" id="barcodeCameraStatus">Camera is not active.</div>
      <button type="button" class="btn btn-primary btn-sm" data-barcode-retry>
        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
        Retry
      </button>
    </div>
  </div>
</div>

<script>
(() => {
  const modal = document.getElementById('barcodeCameraModal');
  const video = document.getElementById('barcodeCameraVideo');
  const fallbackReader = document.getElementById('barcodeFallbackReader');
  const status = document.getElementById('barcodeCameraStatus');
  let activeTarget = null;
  let detector = null;
  let nativeStream = null;
  let nativeTimer = null;
  let zxingReader = null;
  let zxingControls = null;
  let continuousCameraScan = false;
  let lastCameraCode = '';
  let lastCameraScanAt = 0;

  function setStatus(message, state = '') {
    status.textContent = message;
    status.classList.toggle('is-success', state === 'success');
    status.classList.toggle('is-error', state === 'error');
  }

  function cameraConstraints() {
    return {
      video: {
        facingMode: { ideal: 'environment' },
        width: { ideal: 1920 },
        height: { ideal: 1080 },
        focusMode: { ideal: 'continuous' },
      },
      audio: false,
    };
  }

  function handleDetectedCode(code) {
    const cleanCode = String(code || '').trim();
    if (!cleanCode || !activeTarget) {
      return false;
    }

    const now = Date.now();
    if (continuousCameraScan && cleanCode === lastCameraCode && now - lastCameraScanAt < 1500) {
      return false;
    }

    lastCameraCode = cleanCode;
    lastCameraScanAt = now;
    applyBarcodeToTarget(activeTarget, cleanCode);
    setStatus(continuousCameraScan ? `Scanned ${cleanCode}. Move the next product into view.` : `Scanned ${cleanCode}.`, 'success');

    if (!continuousCameraScan) {
      setTimeout(closeScanner, 450);
    }

    return true;
  }

  function applyBarcodeToTarget(target, code) {
    target.value = code;
    target.dispatchEvent(new Event('input', { bubbles: true }));
    target.dispatchEvent(new Event('change', { bubbles: true }));
    target.dispatchEvent(new CustomEvent('barcode-scanned', { bubbles: true, detail: { code } }));
    target.focus();

    if (typeof target.select === 'function') {
      target.select();
    }
  }

  function isTextEntryElement(element) {
    if (!element) {
      return false;
    }

    if (element.isContentEditable) {
      return true;
    }

    return ['INPUT', 'TEXTAREA'].includes(element.tagName);
  }

  function installHardwareScannerCapture() {
    const hardwareTarget = document.querySelector('[data-hardware-barcode-capture]');
    if (!hardwareTarget) {
      return;
    }

    const maxKeyGap = 85;
    const maxScanDuration = 900;
    const minBarcodeLength = 4;
    let buffer = '';
    let startedAt = 0;
    let lastKeyAt = 0;
    let scannerMode = false;
    let sourceElement = null;
    let sourceSnapshot = null;

    function resetBuffer() {
      buffer = '';
      startedAt = 0;
      lastKeyAt = 0;
      scannerMode = false;
      sourceElement = null;
      sourceSnapshot = null;
    }

    function rememberSourceElement() {
      sourceElement = document.activeElement;

      if (!isTextEntryElement(sourceElement) || sourceElement === hardwareTarget || sourceElement.isContentEditable) {
        sourceSnapshot = null;
        return;
      }

      sourceSnapshot = {
        value: sourceElement.value,
        selectionStart: sourceElement.selectionStart,
        selectionEnd: sourceElement.selectionEnd,
      };
    }

    function restoreSourceElement() {
      if (!sourceElement || !sourceSnapshot || sourceElement === hardwareTarget) {
        return;
      }

      sourceElement.value = sourceSnapshot.value;

      if (typeof sourceElement.setSelectionRange === 'function' && sourceSnapshot.selectionStart !== null) {
        sourceElement.setSelectionRange(sourceSnapshot.selectionStart, sourceSnapshot.selectionEnd);
      }

      sourceElement.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function commitHardwareScan(event) {
      const scannedCode = buffer.trim();
      const duration = Date.now() - startedAt;

      if (scannerMode && scannedCode.length >= minBarcodeLength && duration <= maxScanDuration) {
        event.preventDefault();
        restoreSourceElement();
        applyBarcodeToTarget(hardwareTarget, scannedCode);
      }

      resetBuffer();
    }

    document.addEventListener('keydown', (event) => {
      if (modal.classList.contains('is-open') || event.ctrlKey || event.altKey || event.metaKey) {
        return;
      }

      if (event.key === 'Enter' || event.key === 'Tab') {
        commitHardwareScan(event);
        return;
      }

      if (event.key.length !== 1) {
        return;
      }

      const now = Date.now();
      if (!buffer || now - lastKeyAt > maxKeyGap) {
        resetBuffer();
        startedAt = now;
        rememberSourceElement();
      }

      buffer += event.key;
      lastKeyAt = now;

      if (buffer.length >= 3 && now - startedAt <= maxKeyGap * 2) {
        scannerMode = true;
        restoreSourceElement();
      }

      if (scannerMode) {
        event.preventDefault();
      }
    }, true);
  }

  function loadZxingLibrary() {
    if (window.ZXingBrowser) {
      return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
      const existingScript = document.querySelector('script[data-zxing-browser]');
      if (existingScript) {
        existingScript.addEventListener('load', resolve, { once: true });
        existingScript.addEventListener('error', reject, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://unpkg.com/@zxing/browser@latest';
      script.async = true;
      script.dataset.zxingBrowser = 'true';
      script.onload = resolve;
      script.onerror = () => reject(new Error('Could not load the camera scanner. Check internet access or use a USB barcode scanner.'));
      document.head.appendChild(script);
    });
  }

  async function startZxingScanner() {
    await loadZxingLibrary();

    if (!window.ZXingBrowser?.BrowserMultiFormatReader) {
      throw new Error('Camera scanner library loaded, but barcode reader was not available.');
    }

    zxingReader = new ZXingBrowser.BrowserMultiFormatReader();
    setStatus('Scanning. Hold barcode flat, bright, and inside the green line.');

    zxingControls = await zxingReader.decodeFromConstraints(
      cameraConstraints(),
      video,
      (result) => {
        if (!result) {
          return;
        }

        const text = typeof result.getText === 'function' ? result.getText() : String(result.text || result);
        handleDetectedCode(text);
      }
    );
  }

  async function nativeFormats() {
    const preferredFormats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf', 'qr_code'];

    if (!('BarcodeDetector' in window)) {
      return [];
    }

    if (typeof BarcodeDetector.getSupportedFormats !== 'function') {
      return preferredFormats;
    }

    const availableFormats = await BarcodeDetector.getSupportedFormats();
    return preferredFormats.filter((format) => availableFormats.includes(format));
  }

  async function startNativeScanner() {
    const formats = await nativeFormats();
    if (formats.length === 0) {
      throw new Error('Camera barcode scanning is not supported in this browser.');
    }

    detector = new BarcodeDetector({ formats });
    nativeStream = await navigator.mediaDevices.getUserMedia(cameraConstraints());
    video.srcObject = nativeStream;
    await video.play();
    setStatus('Scanning with browser detector. Hold barcode inside the green line.');
    nativeScanLoop();
  }

  async function nativeScanLoop() {
    clearTimeout(nativeTimer);

    if (!nativeStream || !detector) {
      return;
    }

    try {
      const barcodes = await detector.detect(video);
      if (barcodes.length > 0 && handleDetectedCode(barcodes[0].rawValue) && !continuousCameraScan) {
        return;
      }
    } catch (error) {}

    nativeTimer = setTimeout(nativeScanLoop, 140);
  }

  async function openScanner(targetSelector) {
    activeTarget = document.querySelector(targetSelector);
    if (!activeTarget) {
      return;
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    continuousCameraScan = activeTarget.hasAttribute('data-barcode-camera-continuous');
    lastCameraCode = '';
    lastCameraScanAt = 0;
    fallbackReader.innerHTML = '';
    setStatus('Starting camera...');

    try {
      await startZxingScanner();
    } catch (zxingError) {
      try {
        await startNativeScanner();
      } catch (nativeError) {
        setStatus(nativeError.message || zxingError.message || 'Unable to open camera scanner.', 'error');
      }
    }
  }

  async function closeScanner() {
    clearTimeout(nativeTimer);
    nativeTimer = null;

    if (zxingControls) {
      try {
        zxingControls.stop();
      } catch (error) {}
      zxingControls = null;
    }

    if (zxingReader && typeof zxingReader.reset === 'function') {
      try {
        zxingReader.reset();
      } catch (error) {}
    }
    zxingReader = null;

    if (nativeStream) {
      nativeStream.getTracks().forEach((track) => track.stop());
      nativeStream = null;
    }

    video.srcObject = null;
    fallbackReader.innerHTML = '';
    continuousCameraScan = false;
    lastCameraCode = '';
    lastCameraScanAt = 0;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    setStatus('Camera is not active.');
    activeTarget?.focus();
  }

  document.querySelectorAll('[data-open-barcode-camera]').forEach((button) => {
    button.addEventListener('click', () => {
      openScanner(button.dataset.barcodeTarget || 'input[name="code"]');
    });
  });

  document.querySelectorAll('[data-barcode-close]').forEach((button) => {
    button.addEventListener('click', closeScanner);
  });

  document.querySelectorAll('[data-barcode-retry]').forEach((button) => {
    button.addEventListener('click', async () => {
      const selector = activeTarget ? '#' + activeTarget.id : 'input[name="code"]';
      await closeScanner();
      setTimeout(() => openScanner(selector), 150);
    });
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeScanner();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeScanner();
    }
  });

  installHardwareScannerCapture();
})();
</script>
