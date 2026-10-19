/* Nyyo Docs Chatbot front-end. No tracking, no third-party requests in local mode. */
(function () {
  'use strict';
  var cfg = window.NyyoDocsChat;
  if (!cfg) { return; }
  var t = cfg.i18n || {};

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) { e.className = cls; }
    if (text) { e.textContent = text; }
    return e;
  }

  function buildChat(container) {
    var log = el('div', 'nyyo-dc-log');
    log.setAttribute('role', 'log');
    log.setAttribute('aria-live', 'polite');
    var form = el('form', 'nyyo-dc-form');
    var input = el('input', 'nyyo-dc-input');
    input.type = 'text';
    input.maxLength = 500;
    input.placeholder = t.placeholder || '';
    input.setAttribute('aria-label', t.placeholder || '');
    var btn = el('button', 'nyyo-dc-send', t.send || 'Ask');
    btn.type = 'submit';
    form.appendChild(input);
    form.appendChild(btn);
    container.appendChild(log);
    container.appendChild(form);

    function add(role, text) {
      var m = el('div', 'nyyo-dc-msg nyyo-dc-' + role);
      m.appendChild(el('div', 'nyyo-dc-who', role === 'user' ? t.you : t.bot));
      m.appendChild(el('p', '', text));
      log.appendChild(m);
      log.scrollTop = log.scrollHeight;
      return m;
    }
    add('bot', t.welcome || '');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var q = input.value.trim();
      if (!q) { return; }
      input.value = '';
      add('user', q);
      btn.disabled = true;
      var url = cfg.restUrl + (cfg.restUrl.indexOf('?') === -1 ? '?' : '&') + 'q=' + encodeURIComponent(q);
      fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
        .then(function (data) {
          var m = add('bot', data.answer || '');
          if (data.sources && data.sources.length) {
            var box = el('div', 'nyyo-dc-sources');
            box.appendChild(el('strong', '', t.sources || 'Sources'));
            data.sources.forEach(function (s) {
              var row = el('div', 'nyyo-dc-source');
              var a = el('a', '', s.title || s.url);
              if (/^https?:\/\//i.test(s.url)) { a.href = s.url; }
              row.appendChild(a);
              row.appendChild(el('div', 'nyyo-dc-excerpt', s.excerpt || ''));
              box.appendChild(row);
            });
            m.appendChild(box);
          }
          log.scrollTop = log.scrollHeight;
        })
        .catch(function () { add('bot', t.error || 'Error'); })
        .then(function () { btn.disabled = false; });
    });
  }

  function external(container) {
    if (!/^https?:\/\//i.test(cfg.botUrl || '')) { return; }
    var f = el('iframe');
    f.src = cfg.botUrl;
    f.title = t.title || 'Docs chatbot';
    f.loading = 'lazy';
    container.appendChild(f);
  }

  function mount(container) {
    if (cfg.mode === 'external') { external(container); } else { buildChat(container); }
  }

  function init() {
    var inline = document.querySelectorAll('[data-nyyo-docs-chat="inline"]');
    Array.prototype.forEach.call(inline, function (c) { c.classList.add('nyyo-dc-chat'); mount(c); });

    if (!cfg.autoInject) { return; }
    var left = cfg.position === 'bottom-left' ? ' left' : '';
    var fab = el('button', 'nyyo-docs-chat-fab' + left, cfg.buttonLabel || 'Ask');
    fab.type = 'button';
    fab.setAttribute('aria-expanded', 'false');
    var panel = el('div', 'nyyo-docs-chat-panel nyyo-dc-chat' + left);
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', t.title || 'Docs chatbot');
    var close = el('button', 'nyyo-dc-close', '\u00d7');
    close.type = 'button';
    close.setAttribute('aria-label', t.close || 'Close');
    panel.appendChild(close);
    var mounted = false;
    function toggle(open) {
      if (open && !mounted) { mount(panel); mounted = true; } // nothing loads until the visitor opens the chat
      panel.classList.toggle('open', open);
      fab.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    fab.addEventListener('click', function () { toggle(!panel.classList.contains('open')); });
    close.addEventListener('click', function () { toggle(false); });
    document.body.appendChild(panel);
    document.body.appendChild(fab);
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
}());
