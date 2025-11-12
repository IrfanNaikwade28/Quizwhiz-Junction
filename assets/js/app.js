// Global JS: countdown, copy-to-clipboard, small helpers

window.qwz = {
  countdown: null,
};

function startCountdown(durationSeconds, displayEl, onEnd) {
  if (qwz.countdown) clearInterval(qwz.countdown);
  let remaining = durationSeconds;
  const render = () => {
    const m = Math.floor(remaining / 60).toString().padStart(2, '0');
    const s = (remaining % 60).toString().padStart(2, '0');
    if (displayEl) displayEl.textContent = `${m}:${s}`;
  };
  render();
  if (remaining <= 0) {
    if (onEnd) onEnd();
    return;
  }
  qwz.countdown = setInterval(() => {
    remaining -= 1;
    if (remaining <= 0) {
      clearInterval(qwz.countdown);
      render();
      if (onEnd) onEnd();
    } else {
      render();
    }
  }, 1000);
}

function copyToClipboard(text, onDone) {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(() => onDone && onDone(true)).catch(() => onDone && onDone(false));
  } else {
    const ta = document.createElement('textarea');
    ta.value = text; document.body.appendChild(ta); ta.select();
    try {
      const ok = document.execCommand('copy');
      onDone && onDone(ok);
    } catch(e) { onDone && onDone(false); }
    finally { document.body.removeChild(ta); }
  }
}

window.startCountdown = startCountdown;
window.copyToClipboard = copyToClipboard;
