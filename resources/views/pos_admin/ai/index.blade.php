@extends('layouts.pos-admin')

@section('title', 'AI Assistant - ONJECASA POS')
@section('page-icon', 'bi bi-stars')
@section('page-eyebrow', 'Intelligence')
@section('page-title', 'AI Assistant')
@section('page-description', 'Turn live sales, stock, order, and staff data into clear operational answers.')
@section('page-actions')
  <button id="new-ai-conversation" class="btn btn-outline-secondary btn-sm" type="button">
    <i class="bi bi-plus-circle"></i> New conversation
  </button>
@endsection

@section('content')
<section class="ai-workspace" aria-label="AI operations assistant">
  <article class="ai-console">
    <header class="ai-console-header">
      <div class="ai-console-identity">
        <span class="ai-orb"><i class="bi bi-stars" aria-hidden="true"></i></span>
        <div>
          <span class="ai-kicker">ONJECASA POS Intelligence</span>
          <h2>Operations Copilot</h2>
        </div>
      </div>
      <div class="ai-console-status">
        <span class="ai-live"><span class="status-dot"></span> Live data</span>
        <span class="ai-model">{{ env('OPENAI_MODEL', 'gpt-4o-mini') }}</span>
      </div>
    </header>

    <div id="chat-box" class="ai-chat-stream" aria-live="polite"></div>

    <footer class="ai-composer-wrap">
      <div class="ai-composer">
        <span class="ai-composer-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span>
        <input id="chat-input" type="text" autocomplete="off" placeholder="Ask about sales, stock, orders, staff..." aria-label="Message the AI assistant">
        <button id="send-btn" type="button" aria-label="Send message">
          <span>Send</span><i class="bi bi-arrow-up" aria-hidden="true"></i>
        </button>
      </div>
      <p class="ai-composer-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Answers use the latest information available in this POS.</p>
    </footer>
  </article>

  <aside class="ai-prompt-panel">
    <div class="ai-prompt-header">
      <span class="ai-prompt-icon"><i class="bi bi-lightning-charge" aria-hidden="true"></i></span>
      <div>
        <span class="ai-kicker">Start quickly</span>
        <h2>Suggested prompts</h2>
      </div>
    </div>

    <div class="ai-hint-list">
      <button type="button" class="hint-card" data-hint="What are today's sales?">
        <span class="hint-icon hint-sales"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
        <span class="hint-copy"><small>Sales summary</small><strong>What are today's sales?</strong></span>
        <i class="bi bi-arrow-up-right hint-arrow" aria-hidden="true"></i>
      </button>

      <button type="button" class="hint-card" data-hint="Show items to restock first.">
        <span class="hint-icon hint-stock"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
        <span class="hint-copy"><small>Stock watch</small><strong>Show items to restock first.</strong></span>
        <i class="bi bi-arrow-up-right hint-arrow" aria-hidden="true"></i>
      </button>

      <button type="button" class="hint-card" data-hint="What needs attention now?">
        <span class="hint-icon hint-operations"><i class="bi bi-exclamation-diamond" aria-hidden="true"></i></span>
        <span class="hint-copy"><small>Operations</small><strong>What needs attention now?</strong></span>
        <i class="bi bi-arrow-up-right hint-arrow" aria-hidden="true"></i>
      </button>

      <button type="button" class="hint-card" data-hint="Compare cash and mobile money sales.">
        <span class="hint-icon hint-payments"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
        <span class="hint-copy"><small>Payments</small><strong>Compare cash and mobile money.</strong></span>
        <i class="bi bi-arrow-up-right hint-arrow" aria-hidden="true"></i>
      </button>
    </div>

    <div class="ai-scope">
      <span><i class="bi bi-shield-check" aria-hidden="true"></i></span>
      <div><strong>System-aware</strong><small>Sales, inventory, orders, payments, staff, and audit activity.</small></div>
    </div>
  </aside>
</section>

<style>
  #ai-assistant-wrapper { display: none; }

  .ai-workspace {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(280px, .8fr);
    gap: 1rem;
    min-height: 610px;
  }

  .ai-console,
  .ai-prompt-panel {
    border: 1px solid var(--admin-border);
    border-radius: 18px;
    background: var(--admin-surface, #fff);
    box-shadow: var(--admin-shadow);
    overflow: hidden;
  }

  .ai-console {
    display: flex;
    min-width: 0;
    flex-direction: column;
  }

  .ai-console-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--admin-border);
    background: linear-gradient(135deg, rgba(219, 234, 254, .66), rgba(255, 255, 255, .96) 55%, rgba(209, 250, 229, .45));
  }

  .ai-console-identity,
  .ai-prompt-header {
    display: flex;
    align-items: center;
    gap: .75rem;
  }

  .ai-orb,
  .ai-prompt-icon {
    display: inline-grid;
    flex: 0 0 auto;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    color: #fff;
    background: linear-gradient(145deg, #2563eb, #0f766e);
    box-shadow: 0 10px 24px rgba(37, 99, 235, .2);
  }

  .ai-kicker {
    display: block;
    margin-bottom: .16rem;
    color: #2563eb;
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
  }

  .ai-console h2,
  .ai-prompt-panel h2 {
    margin: 0;
    color: var(--admin-text);
    font-size: 1rem;
    font-weight: 800;
  }

  .ai-console-status {
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .ai-live,
  .ai-model {
    display: inline-flex;
    align-items: center;
    gap: .38rem;
    padding: .35rem .6rem;
    border: 1px solid var(--admin-border);
    border-radius: 999px;
    color: var(--admin-muted);
    background: rgba(255, 255, 255, .78);
    font-size: .7rem;
    font-weight: 700;
  }

  .ai-live .status-dot {
    width: 7px;
    height: 7px;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, .12);
  }

  .ai-chat-stream {
    position: relative;
    flex: 1;
    min-height: 410px;
    max-height: 520px;
    overflow-y: auto;
    padding: 1.25rem;
    background:
      radial-gradient(circle at 1px 1px, rgba(148, 163, 184, .2) 1px, transparent 0) 0 0 / 22px 22px,
      linear-gradient(180deg, #f8fbff, #fff 48%);
  }

  .ai-message {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    margin-bottom: 1rem;
  }

  .ai-message.user { flex-direction: row-reverse; }

  .ai-message-avatar {
    display: inline-grid;
    flex: 0 0 auto;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: #1d4ed8;
    background: #dbeafe;
    font-size: .78rem;
  }

  .ai-message.user .ai-message-avatar {
    color: #fff;
    background: #0f766e;
  }

  .ai-message-content { max-width: min(76%, 620px); }

  .ai-message-bubble {
    padding: .72rem .85rem;
    border: 1px solid var(--admin-border);
    border-radius: 4px 14px 14px;
    color: var(--admin-text);
    background: rgba(255, 255, 255, .96);
    box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
    font-size: .82rem;
    line-height: 1.55;
    white-space: pre-wrap;
  }

  .ai-message.user .ai-message-bubble {
    border-color: transparent;
    border-radius: 14px 4px 14px 14px;
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #0f766e);
  }

  .ai-message-meta {
    display: block;
    margin-top: .3rem;
    color: var(--admin-muted);
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .ai-message.user .ai-message-meta { text-align: right; }

  .ai-composer-wrap {
    padding: .85rem 1rem .75rem;
    border-top: 1px solid var(--admin-border);
    background: var(--admin-surface);
  }

  .ai-composer {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: .55rem;
    padding: .42rem .45rem .42rem .75rem;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    background: var(--admin-surface-soft);
    transition: border-color .16s ease, box-shadow .16s ease;
  }

  .ai-composer:focus-within {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
  }

  .ai-composer-icon { color: #64748b; }

  .ai-composer input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    color: var(--admin-text);
    background: transparent;
    font-size: .82rem;
  }

  .ai-composer button {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    min-height: 36px;
    padding: .45rem .72rem;
    border: 0;
    border-radius: 10px;
    color: #fff;
    background: #0f172a;
    font-size: .75rem;
    font-weight: 800;
  }

  .ai-composer button:disabled { cursor: wait; opacity: .6; }
  .ai-composer button .spin { animation: ai-spin .8s linear infinite; }
  @keyframes ai-spin { to { transform: rotate(360deg); } }

  .ai-composer-note {
    margin: .48rem 0 0;
    color: var(--admin-muted);
    font-size: .66rem;
    text-align: center;
  }

  .ai-prompt-panel {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    background: linear-gradient(180deg, var(--admin-surface), var(--admin-surface-soft));
  }

  .ai-prompt-header {
    padding: .15rem .1rem .9rem;
    border-bottom: 1px solid var(--admin-border);
  }

  .ai-prompt-icon {
    width: 38px;
    height: 38px;
    color: #b45309;
    background: #fef3c7;
    box-shadow: none;
  }

  .ai-hint-list {
    display: grid;
    gap: .65rem;
    margin-top: .85rem;
  }

  .hint-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: .7rem;
    width: 100%;
    padding: .75rem;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    color: inherit;
    text-align: left;
    background: var(--admin-surface);
    transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
  }

  .hint-card:hover,
  .hint-card:focus-visible {
    border-color: rgba(37, 99, 235, .42);
    outline: 0;
    box-shadow: 0 10px 24px rgba(37, 99, 235, .08);
    transform: translateY(-2px);
  }

  .hint-icon {
    display: inline-grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    color: #1d4ed8;
    background: #dbeafe;
  }

  .hint-stock { color: #047857; background: #d1fae5; }
  .hint-operations { color: #b45309; background: #fef3c7; }
  .hint-payments { color: #be123c; background: #ffe4e6; }

  .hint-copy { display: grid; gap: .12rem; min-width: 0; }
  .hint-copy small { color: var(--admin-muted); font-size: .62rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
  .hint-copy strong { color: var(--admin-text); font-size: .76rem; line-height: 1.35; }
  .hint-arrow { color: #94a3b8; font-size: .75rem; }

  .ai-scope {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
    margin-top: auto;
    padding: .85rem;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    color: #1e3a8a;
    background: #eff6ff;
  }

  .ai-scope > span { font-size: 1rem; }
  .ai-scope div { display: grid; gap: .16rem; }
  .ai-scope strong { font-size: .72rem; }
  .ai-scope small { color: #475569; font-size: .66rem; line-height: 1.4; }

  html[data-theme="dark"] .ai-console-header,
  html[data-theme="dark"] .ai-prompt-panel,
  html[data-theme="dark"] .ai-composer-wrap,
  html[data-theme="dark"] .ai-composer,
  html[data-theme="dark"] .hint-card,
  html[data-theme="dark"] .ai-message-bubble {
    background: var(--admin-surface);
  }

  html[data-theme="dark"] .ai-chat-stream {
    background: var(--admin-bg);
  }

  @media (max-width: 1199.98px) {
    .ai-workspace { grid-template-columns: 1fr; }
    .ai-scope { margin-top: 1rem; }
  }

  @media (max-width: 575.98px) {
    .ai-workspace { min-height: auto; }
    .ai-console-header { align-items: flex-start; flex-direction: column; }
    .ai-chat-stream { min-height: 360px; padding: .9rem; }
    .ai-message-content { max-width: 86%; }
    .ai-composer button span { display: none; }
    .ai-composer button { width: 36px; justify-content: center; padding-inline: 0; }
  }
</style>

<script>
(() => {
  const chatBox = document.getElementById('chat-box');
  const chatInput = document.getElementById('chat-input');
  const sendBtn = document.getElementById('send-btn');
  const newConversationBtn = document.getElementById('new-ai-conversation');
  const hintCards = document.querySelectorAll('[data-hint]');
  let history = [];

  const addMessage = (role, text) => {
    const row = document.createElement('div');
    const avatar = document.createElement('span');
    const content = document.createElement('div');
    const bubble = document.createElement('div');
    const meta = document.createElement('small');

    row.className = `ai-message ${role}`;
    avatar.className = 'ai-message-avatar';
    avatar.innerHTML = role === 'user' ? '<i class="bi bi-person"></i>' : '<i class="bi bi-stars"></i>';
    content.className = 'ai-message-content';
    bubble.className = 'ai-message-bubble';
    bubble.textContent = text;
    meta.className = 'ai-message-meta';
    meta.textContent = `${role === 'user' ? 'You' : 'ONJECASA POS AI'} | ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

    content.append(bubble, meta);
    row.append(avatar, content);
    chatBox.appendChild(row);
    chatBox.scrollTop = chatBox.scrollHeight;
    return row;
  };

  const resetConversation = () => {
    history = [];
    chatBox.replaceChildren();
    addMessage('assistant', 'I am connected to your POS data. Ask me about sales, inventory, orders, payments, staff, or anything that needs attention.');
    chatInput.value = '';
    chatInput.focus();
  };

  const setSending = (sending) => {
    sendBtn.disabled = sending;
    sendBtn.querySelector('i').className = sending ? 'bi bi-arrow-repeat spin' : 'bi bi-arrow-up';
  };

  const send = async () => {
    const message = chatInput.value.trim();
    if (!message || sendBtn.disabled) return;

    addMessage('user', message);
    chatInput.value = '';
    setSending(true);
    const thinking = addMessage('assistant', 'Thinking...');

    try {
      const response = await fetch('{{ route('pos.admin.ai.chat') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message, history })
      });

      const data = await response.json();
      thinking.remove();
      const reply = data.reply || data.error || 'No response was returned.';
      addMessage('assistant', reply);

      if (response.ok) {
        history.push({ role: 'user', content: message }, { role: 'assistant', content: reply });
        if (history.length > 14) history.splice(0, 2);
      }
    } catch (error) {
      thinking.remove();
      addMessage('assistant', 'I could not reach the AI service. Please try again.');
    } finally {
      setSending(false);
      chatInput.focus();
    }
  };

  sendBtn.addEventListener('click', send);
  chatInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') send();
  });
  newConversationBtn?.addEventListener('click', resetConversation);
  hintCards.forEach((card) => {
    card.addEventListener('click', () => {
      chatInput.value = card.dataset.hint || '';
      chatInput.focus();
    });
  });

  resetConversation();
})();
</script>
@endsection

