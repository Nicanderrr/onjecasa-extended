@auth
<script>
document.addEventListener('DOMContentLoaded', function () {
  const snapshotUrl = @json(route('notifications.snapshot'));
  const userKey = 'notification-sound-latest-{{ auth()->id() }}';
  const allowedTypes = ['new_order', 'online_order_branch'];
  let audioReady = false;
  let audioContext = null;

  function unlockAudio() {
    if (audioReady) return;
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;
    audioContext = audioContext || new AudioContextClass();
    if (audioContext.state === 'suspended') {
      audioContext.resume().catch(function () {});
    }
    audioReady = true;
  }

  function playNotificationSound() {
    if (!audioReady || !audioContext) return;
    const now = audioContext.currentTime;
    [0, 0.16].forEach(function (offset) {
      const oscillator = audioContext.createOscillator();
      const gain = audioContext.createGain();
      oscillator.type = 'sine';
      oscillator.frequency.setValueAtTime(880, now + offset);
      oscillator.frequency.exponentialRampToValueAtTime(1320, now + offset + 0.08);
      gain.gain.setValueAtTime(0.0001, now + offset);
      gain.gain.exponentialRampToValueAtTime(0.18, now + offset + 0.015);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + offset + 0.14);
      oscillator.connect(gain);
      gain.connect(audioContext.destination);
      oscillator.start(now + offset);
      oscillator.stop(now + offset + 0.15);
    });
  }

  async function checkNotifications() {
    try {
      const response = await fetch(snapshotUrl, {headers: {'Accept': 'application/json'}});
      if (!response.ok) return;
      const data = await response.json();
      const latestId = data.latest_unread_id || '';
      const previousId = localStorage.getItem(userKey) || '';

      if (!previousId && latestId) {
        localStorage.setItem(userKey, latestId);
        return;
      }

      if (latestId && latestId !== previousId && allowedTypes.includes(data.latest_type)) {
        localStorage.setItem(userKey, latestId);
        playNotificationSound();
      }
    } catch (error) {
      //
    }
  }

  ['click', 'keydown', 'touchstart'].forEach(function (eventName) {
    window.addEventListener(eventName, unlockAudio, {once: true, passive: true});
  });

  checkNotifications();
  setInterval(checkNotifications, 15000);
});
</script>
@endauth
