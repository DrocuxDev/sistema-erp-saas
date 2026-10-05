const Toast = (() => {
  const ICONS = {
    success: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd"/></svg>',
    error: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/></svg>',
    warning: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.4 2.7a1.75 1.75 0 013.2 0l7.3 13.9A1.75 1.75 0 0118.3 19H1.7a1.75 1.75 0 01-1.6-2.4L9.4 2.7zM10 7a.9.9 0 00-.9.9v3.6a.9.9 0 001.8 0V7.9A.9.9 0 0010 7zm0 8.2a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>',
    info: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11.5a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h.5a1 1 0 100-2H10V10a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
  };

  let container = null;
  let position = 'bottom-right'; // top-right | top-left | bottom-right | bottom-left | top-center | bottom-center

  function getContainer() {
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'false');
      document.body.appendChild(container);
    }
    applyPosition();
    return container;
  }

  function applyPosition() {
    const pos = { top: 'auto', bottom: 'auto', left: 'auto', right: 'auto', alignItems: 'flex-end' };
    if (position.includes('top')) pos.top = '0';
    if (position.includes('bottom')) pos.bottom = '0';
    if (position.includes('left')) { pos.left = '0'; pos.alignItems = 'flex-start'; }
    if (position.includes('right')) { pos.right = '0'; pos.alignItems = 'flex-end'; }
    if (position.includes('center')) { pos.left = '0'; pos.right = '0'; pos.alignItems = 'center'; }
    Object.assign(container.style, pos);
  }

  function show(type, title, message = '', opts = {}) {
    const { duration = 4500, style = 'soft', dismissible = true } = opts;
    const el = document.createElement('div');
    el.className = `toast toast--${type}${style === 'solid' ? ' toast--solid' : ''}`;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');

    el.innerHTML = `
      <span class="toast__icon">${ICONS[type] || ICONS.info}</span>
      <div class="toast__body">
        <p class="toast__title">${title}</p>
        ${message ? `<p class="toast__message">${message}</p>` : ''}
      </div>
      ${dismissible ? '<button class="toast__close" aria-label="Cerrar"><svg viewBox="0 0 20 20" fill="none"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>' : ''}
      ${duration ? `<span class="toast__progress" style="animation-duration:${duration}ms"></span>` : ''}
    `;

    const remove = () => {
      el.classList.add('is-leaving');
      el.addEventListener('animationend', () => el.remove(), { once: true });
    };

    if (dismissible) el.querySelector('.toast__close').addEventListener('click', remove);

    let timer;
    if (duration) {
      timer = setTimeout(remove, duration);
      el.addEventListener('mouseenter', () => clearTimeout(timer));
      el.addEventListener('mouseleave', () => { timer = setTimeout(remove, 1200); });
    }

    getContainer().appendChild(el);
    return { close: remove };
  }

  return {
    setPosition: (p) => { position = p; if (container) applyPosition(); },
    show: (title, message, opts) => show('info', title, message, opts),
    success: (title, message, opts) => show('success', title, message, opts),
    error: (title, message, opts) => show('error', title, message, opts),
    warning: (title, message, opts) => show('warning', title, message, opts),
    info: (title, message, opts) => show('info', title, message, opts),
  };
})();

const Dialog = (() => {
  let lastFocused = null;

  function open({
    title,
    description = '',
    confirmText = 'Confirmar',
    cancelText = 'Cancelar',
    danger = false,
    dismissible = true,
  } = {}) {
    return new Promise((resolve) => {
      lastFocused = document.activeElement;

      const overlay = document.createElement('div');
      overlay.className = 'dialog-overlay';

      const dialog = document.createElement('div');
      dialog.className = `dialog${danger ? ' dialog--danger' : ''}`;
      dialog.setAttribute('role', 'alertdialog');
      dialog.setAttribute('aria-modal', 'true');
      dialog.setAttribute('aria-labelledby', 'dialog-title');
      if (description) dialog.setAttribute('aria-describedby', 'dialog-desc');

      dialog.innerHTML = `
        <h2 class="dialog__title" id="dialog-title">${title}</h2>
        ${description ? `<p class="dialog__description" id="dialog-desc">${description}</p>` : ''}
        <div class="dialog__actions">
          <button class="dialog__btn dialog__btn--cancel" type="button">${cancelText}</button>
          <button class="dialog__btn dialog__btn--confirm" type="button">${confirmText}</button>
        </div>
      `;

      overlay.appendChild(dialog);
      document.body.appendChild(overlay);
      document.body.classList.add('dialog-open');

      const confirmBtn = dialog.querySelector('.dialog__btn--confirm');
      const cancelBtn = dialog.querySelector('.dialog__btn--cancel');
      confirmBtn.focus();

      const close = (result) => {
        overlay.classList.add('is-leaving');
        overlay.addEventListener('animationend', () => {
          overlay.remove();
          document.body.classList.remove('dialog-open');
          if (lastFocused) lastFocused.focus();
        }, { once: true });
        document.removeEventListener('keydown', onKeydown);
        resolve(result);
      };

      confirmBtn.addEventListener('click', () => close(true));
      cancelBtn.addEventListener('click', () => close(false));
      if (dismissible) {
        overlay.addEventListener('click', (e) => { if (e.target === overlay) close(false); });
      }

      function onKeydown(e) {
        if (e.key === 'Escape' && dismissible) close(false);
        if (e.key === 'Tab') {
          const focusables = dialog.querySelectorAll('button');
          const first = focusables[0];
          const last = focusables[focusables.length - 1];
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
      }
      document.addEventListener('keydown', onKeydown);
    });
  }

  return {
    confirm: (title, description, opts = {}) => open({ title, description, ...opts }),
    danger: (title, description, opts = {}) => open({ title, description, danger: true, confirmText: 'Eliminar', ...opts }),
  };
})();

/* ===== Integración ERP: reemplazos drop-in para onsubmit/onclick nativos ===== */

async function confirmFormSubmit(event, title, description, okText = 'Eliminar', danger = true) {
  event.preventDefault();
  const call = danger
    ? Dialog.danger(title, description, { confirmText: okText })
    : Dialog.confirm(title, description, { confirmText: okText });
  if (await call) event.target.submit();
}

async function submitAfterConfirm(formId, title, description, okText = 'Eliminar', danger = true) {
  const call = danger
    ? Dialog.danger(title, description, { confirmText: okText })
    : Dialog.confirm(title, description, { confirmText: okText });
  if (await call) document.getElementById(formId).submit();
}

async function confirmStubSubmit(btn, title, description) {
  const ok = await Dialog.danger(title, description, { confirmText: 'Eliminar' });
  if (!ok) return;
  const form = document.getElementById(btn.getAttribute('form'));
  if (btn.hasAttribute('formaction')) form.action = btn.getAttribute('formaction');
  form.submit();
}

/* ===== Anti guardado sin cambios (opt-in: <form data-dirty-check>) ===== */
(function initDirtyCheck() {
  function fingerprint(form) {
    const parts = [];
    for (const el of form.elements) {
      if (!el.name || el.disabled) continue;
      if (el.type === 'file') {
        const f = el.files[0];
        parts.push(el.name + '=' + (f ? f.name + '|' + f.size + '|' + f.lastModified : ''));
      } else if (el.type === 'checkbox' || el.type === 'radio') {
        if (el.checked) parts.push(el.name + '=' + el.value);
      } else if (el.tagName === 'SELECT' && el.multiple) {
        Array.from(el.selectedOptions).map((o) => o.value).sort()
          .forEach((v) => parts.push(el.name + '=' + v));
      } else {
        parts.push(el.name + '=' + el.value);
      }
    }
    return parts.sort().join('&');
  }

  document.querySelectorAll('form[data-dirty-check]').forEach((form) => {
    if (form.dataset.dirtyArmed) return;
    form.dataset.dirtyArmed = '1';
    const initial = fingerprint(form);
    form.addEventListener('submit', (e) => {
      if (fingerprint(form) === initial) {
        e.preventDefault();
        Toast.info('Sin cambios para guardar', 'Modifica algún campo antes de guardar.');
      }
    });
  });
})();
