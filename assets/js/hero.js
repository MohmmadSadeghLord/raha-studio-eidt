document.addEventListener('DOMContentLoaded', function() {
    const body   = document.getElementById('terminal-body');
    const codeEl = document.getElementById('terminal-code');
    const source = document.getElementById('hero-terminal-source');
    
    if (!body || !codeEl || !source) return;

    const CODE = source.textContent;
    const lines = CODE.split('\n');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // خواندن سرعت از پیشخوان
    const cfg = window.RahaHeroConfig || {};
    let baseDelay = 22; // عادی
    if (cfg.typingSpeed === 'fast') baseDelay = 8;
    if (cfg.typingSpeed === 'slow') baseDelay = 50;

    const CHAR_DELAY   = baseDelay;
    const CHAR_JITTER  = Math.floor(baseDelay / 2);
    const LINE_DELAY   = 55;
    const CYCLE_PAUSE  = 2800;
    const START_DELAY  = 1000;

    let lineIdx = 0, charIdx = 0, lineEl = null, textEl = null;

    function getLineClass(line) {
        const t = line.trim();
        if (t.startsWith('$ '))   return 't-prompt';
        if (t.startsWith('//'))   return 't-cmt';
        if (t.startsWith('<!--')) return 't-cmt';
        if (t.startsWith('/*'))   return 't-cmt';
        return 't-code';
    }
    function scrollToBottom(){ body.scrollTop = body.scrollHeight; }

    function tick() {
        if (lineIdx >= lines.length) { setTimeout(restart, CYCLE_PAUSE); return; }
        const line = lines[lineIdx];
        if (!lineEl) {
            lineEl = document.createElement('div');
            lineEl.className = 't-line ' + getLineClass(line);
            textEl = document.createElement('span');
            textEl.className = 't-text';
            lineEl.appendChild(textEl);
            codeEl.appendChild(lineEl);
        }
        if (charIdx < line.length) {
            textEl.textContent += line.charAt(charIdx);
            charIdx++;
            scrollToBottom();
            setTimeout(tick, CHAR_DELAY + Math.random() * CHAR_JITTER);
        } else {
            lineIdx++; charIdx = 0; lineEl = null; textEl = null;
            scrollToBottom();
            setTimeout(tick, LINE_DELAY);
        }
    }
    function restart() {
        codeEl.innerHTML = '';
        lineIdx = 0; charIdx = 0; lineEl = null; textEl = null;
        scrollToBottom();
        setTimeout(tick, 400);
    }

    if (reduced) {
        lines.forEach((line) => {
            const el = document.createElement('div');
            el.className = 't-line ' + getLineClass(line);
            const sp = document.createElement('span');
            sp.className = 't-text';
            sp.textContent = line;
            el.appendChild(sp);
            codeEl.appendChild(el);
        });
        scrollToBottom();
        return;
    }

    setTimeout(tick, START_DELAY);
});