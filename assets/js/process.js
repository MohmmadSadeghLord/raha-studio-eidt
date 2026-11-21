document.addEventListener('DOMContentLoaded', function () {
  var wrapper = document.querySelector('.process-page-wrapper .hero__panel-wrapper');
  if (!wrapper) return;

  var panel   = wrapper.querySelector('.hero__panel');
  var sticker = wrapper.querySelector('.hero__sticker');
  var reduce  = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reduce.matches || !panel) return;

  var raf = null;
  var s = { rx: 0, ry: 0, mx: 50, my: 50, sx: 0, sy: 0 };

  function render() {
    raf = null;
    panel.style.setProperty('--rx', s.rx.toFixed(2) + 'deg');
    panel.style.setProperty('--ry', s.ry.toFixed(2) + 'deg');
    panel.style.setProperty('--mx', s.mx.toFixed(1) + '%');
    panel.style.setProperty('--my', s.my.toFixed(1) + '%');
    if (sticker) {
      sticker.style.setProperty('--sx', s.sx.toFixed(2) + 'px');
      sticker.style.setProperty('--sy', s.sy.toFixed(2) + 'px');
    }
  }
  function schedule() { if (!raf) raf = requestAnimationFrame(render); }

  wrapper.addEventListener('pointermove', function (e) {
    if (e.pointerType === 'touch') return;

    var r  = wrapper.getBoundingClientRect();
    var px = (e.clientX - r.left) / r.width;
    var py = (e.clientY - r.top)  / r.height;
    px = Math.min(1, Math.max(0, px));
    py = Math.min(1, Math.max(0, py));

    s.rx = (0.5 - py) * 15;
    s.ry = (px - 0.5) * 18;
    s.mx = px * 100;
    s.my = py * 100;
    s.sx = (px - 0.5) * -12;
    s.sy = (py - 0.5) * -10;

    panel.classList.add('is-tilting');
    schedule();
  });

  function reset() {
    s.rx = 0; s.ry = 0; s.mx = 50; s.my = 50; s.sx = 0; s.sy = 0;
    panel.classList.remove('is-tilting');
    schedule();
  }

  wrapper.addEventListener('pointerleave', reset);
  wrapper.addEventListener('pointercancel', reset);
  wrapper.addEventListener('blur', reset, true);
});