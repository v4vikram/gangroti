/**
 * Slider - dependency-free carousel for Gangotri Expeditions.
 *
 * Replaces Owl/Slick (both need jQuery). ~4 KB unminified, no build step.
 *
 * Markup:
 *   <div class="slider" data-slider data-per-view="1" data-per-view-md="2"
 *        data-per-view-lg="3" data-gap="24" data-autoplay="5000" data-loop>
 *     <div class="slider-viewport">
 *       <ul class="slider-track">
 *         <li class="slider-slide">...</li>
 *       </ul>
 *     </div>
 *     <button data-slider-prev>...</button>
 *     <button data-slider-next>...</button>
 *     <div data-slider-dots></div>
 *   </div>
 *
 * Behaviour notes:
 * - `data-loop` clones `perView` slides on each side for a seamless wrap.
 * - Autoplay only runs while the slider is on screen, the tab is visible, and
 *   the pointer/focus is elsewhere. Respects prefers-reduced-motion.
 */

const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

export class Slider {
  constructor(root) {
    this.root = root;
    this.viewport = root.querySelector('.slider-viewport');
    this.track = root.querySelector('.slider-track');
    this.prevBtn = root.querySelector('[data-slider-prev]');
    this.nextBtn = root.querySelector('[data-slider-next]');
    this.dotsBox = root.querySelector('[data-slider-dots]');

    if (!this.viewport || !this.track) return;

    this.originals = [...this.track.children];
    this.count = this.originals.length;
    if (!this.count) return;

    this.gap = Number(root.dataset.gap ?? 24);
    this.loop = root.hasAttribute('data-loop') && this.count > 1;
    this.autoplayDelay = Number(root.dataset.autoplay ?? 0);

    this.index = 0;
    this.perView = 1;
    this.cloneCount = 0;
    this.animating = false;
    this.timer = null;
    this.paused = false;
    this.visible = false;

    this.#setupA11y();
    this.#bindEvents();
    this.layout();
  }

  /* ---------------------------------------------------------------- setup */

  #setupA11y() {
    this.root.setAttribute('role', 'region');
    this.root.setAttribute('aria-roledescription', 'carousel');
    if (!this.root.hasAttribute('aria-label')) {
      this.root.setAttribute('aria-label', 'Slider');
    }
    this.viewport.setAttribute('aria-live', this.autoplayDelay ? 'off' : 'polite');
    this.originals.forEach((slide, i) => {
      slide.setAttribute('role', 'group');
      slide.setAttribute('aria-roledescription', 'slide');
      slide.setAttribute('aria-label', `${i + 1} of ${this.count}`);
    });
  }

  #bindEvents() {
    this.prevBtn?.addEventListener('click', () => this.go(this.index - 1, true));
    this.nextBtn?.addEventListener('click', () => this.go(this.index + 1, true));

    this.root.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { e.preventDefault(); this.go(this.index - 1, true); }
      if (e.key === 'ArrowRight') { e.preventDefault(); this.go(this.index + 1, true); }
    });

    this.track.addEventListener('transitionend', (e) => {
      if (e.target === this.track && e.propertyName === 'transform') this.#onSettle();
    });

    // Pause autoplay while the user is interacting or the tab is hidden.
    for (const evt of ['pointerenter', 'focusin']) {
      this.root.addEventListener(evt, () => { this.paused = true; this.#stopTimer(); });
    }
    for (const evt of ['pointerleave', 'focusout']) {
      this.root.addEventListener(evt, () => { this.paused = false; this.#startTimer(); });
    }
    document.addEventListener('visibilitychange', () => {
      document.hidden ? this.#stopTimer() : this.#startTimer();
    });

    // Only autoplay what is actually on screen.
    if (this.autoplayDelay) {
      new IntersectionObserver(([entry]) => {
        this.visible = entry.isIntersecting;
        this.visible ? this.#startTimer() : this.#stopTimer();
      }, { threshold: 0.25 }).observe(this.root);
    }

    let raf = 0;
    new ResizeObserver(() => {
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(() => this.layout());
    }).observe(this.root);

    this.#bindDrag();
  }

  /* --------------------------------------------------------------- layout */

  #resolvePerView() {
    const w = window.innerWidth;
    const d = this.root.dataset;
    let n = Number(d.perView ?? 1);
    if (w >= 640 && d.perViewSm) n = Number(d.perViewSm);
    if (w >= 768 && d.perViewMd) n = Number(d.perViewMd);
    if (w >= 1024 && d.perViewLg) n = Number(d.perViewLg);
    if (w >= 1280 && d.perViewXl) n = Number(d.perViewXl);
    return Math.min(n, this.count);
  }

  layout() {
    const perView = this.#resolvePerView();
    const rebuild = perView !== this.perView;
    this.perView = perView;

    if (rebuild) this.#buildClones();

    this.maxIndex = this.loop ? this.count - 1 : Math.max(0, this.count - this.perView);
    this.index = Math.min(this.index, this.maxIndex);

    const total = this.perView;
    const slideWidth = (this.viewport.clientWidth - this.gap * (total - 1)) / total;
    this.slideWidth = slideWidth;

    for (const slide of this.track.children) {
      slide.style.width = `${slideWidth}px`;
      slide.style.marginRight = `${this.gap}px`;
    }

    this.#apply(false);
    this.#renderDots(rebuild);
    this.#syncButtons();
    this.#startTimer();
  }

  /**
   * For a seamless wrap we mirror `perView` slides on each side, so the track
   * always has real content to slide into before we silently snap back.
   */
  #buildClones() {
    this.track.querySelectorAll('[data-clone]').forEach((n) => n.remove());
    this.cloneCount = 0;
    if (!this.loop) return;

    const n = Math.min(this.perView, this.count);
    const clone = (node) => {
      const c = node.cloneNode(true);
      c.setAttribute('data-clone', '');
      c.setAttribute('aria-hidden', 'true');
      c.removeAttribute('role');
      c.querySelectorAll('a,button,input,select,textarea').forEach((el) => {
        el.setAttribute('tabindex', '-1');
      });
      return c;
    };

    const head = this.originals.slice(-n).map(clone);
    const tail = this.originals.slice(0, n).map(clone);
    this.track.prepend(...head);
    this.track.append(...tail);
    this.cloneCount = n;
  }

  /* ------------------------------------------------------------ movement */

  #offsetFor(index) {
    return -(index + this.cloneCount) * (this.slideWidth + this.gap);
  }

  #apply(animate) {
    this.track.style.transition = animate && !REDUCED_MOTION.matches
      ? 'transform 0.45s cubic-bezier(0.22, 1, 0.36, 1)'
      : 'none';
    this.track.style.transform = `translate3d(${this.#offsetFor(this.index)}px, 0, 0)`;
  }

  go(target, fromUser = false) {
    if (this.animating) return;

    if (this.loop) {
      // Allow one step outside the real range; #onSettle snaps it back.
      if (target < -1 || target > this.maxIndex + 1) return;
    } else {
      target = Math.max(0, Math.min(target, this.maxIndex));
      if (target === this.index) return;
    }

    this.animating = true;
    this.index = target;
    this.#apply(true);
    this.#syncButtons();
    this.#syncDots();

    if (fromUser) this.#restartTimer();

    // transitionend does not fire when reduced motion collapses the duration.
    if (REDUCED_MOTION.matches) this.#onSettle();
  }

  #onSettle() {
    this.animating = false;
    if (!this.loop) return;

    if (this.index < 0) {
      this.index = this.maxIndex;
      this.#apply(false);
    } else if (this.index > this.maxIndex) {
      this.index = 0;
      this.#apply(false);
    }
    this.#syncDots();
  }

  /* ---------------------------------------------------------------- drag */

  #bindDrag() {
    let startX = 0;
    let startOffset = 0;
    let dragging = false;
    let pointerId = null;

    const onDown = (e) => {
      if (e.button !== undefined && e.button !== 0) return;
      if (e.target.closest('a, button')) return;
      dragging = true;
      pointerId = e.pointerId;
      startX = e.clientX;
      startOffset = this.#offsetFor(this.index);
      this.track.style.transition = 'none';
      this.#stopTimer();
    };

    const onMove = (e) => {
      if (!dragging) return;
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 8) this.viewport.setPointerCapture?.(pointerId);
      this.track.style.transform = `translate3d(${startOffset + dx}px, 0, 0)`;
    };

    const onUp = (e) => {
      if (!dragging) return;
      dragging = false;
      this.viewport.releasePointerCapture?.(pointerId);

      const dx = e.clientX - startX;
      const threshold = Math.min(80, this.slideWidth * 0.2);
      if (dx <= -threshold) this.go(this.index + 1, true);
      else if (dx >= threshold) this.go(this.index - 1, true);
      else this.#apply(true);
    };

    this.viewport.addEventListener('pointerdown', onDown);
    this.viewport.addEventListener('pointermove', onMove);
    this.viewport.addEventListener('pointerup', onUp);
    this.viewport.addEventListener('pointercancel', onUp);
    // Let the browser handle vertical scrolling, we only claim horizontal.
    this.viewport.style.touchAction = 'pan-y';
    this.viewport.addEventListener('dragstart', (e) => e.preventDefault());
  }

  /* ----------------------------------------------------------- ui state */

  #renderDots(rebuild) {
    if (!this.dotsBox) return;
    const pages = this.loop ? this.count : this.maxIndex + 1;
    if (!rebuild && this.dotsBox.children.length === pages) return this.#syncDots();

    this.dotsBox.replaceChildren();
    this.dotsBox.setAttribute('role', 'tablist');
    for (let i = 0; i < pages; i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'slider-dot';
      dot.setAttribute('role', 'tab');
      dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
      dot.addEventListener('click', () => this.go(i, true));
      this.dotsBox.append(dot);
    }
    this.#syncDots();
  }

  #syncDots() {
    if (!this.dotsBox) return;
    const active = (this.index + this.count) % this.count;
    [...this.dotsBox.children].forEach((dot, i) => {
      const on = i === active;
      dot.setAttribute('aria-selected', String(on));
      dot.classList.toggle('is-active', on);
    });
  }

  #syncButtons() {
    if (this.loop) return;
    if (this.prevBtn) this.prevBtn.disabled = this.index <= 0;
    if (this.nextBtn) this.nextBtn.disabled = this.index >= this.maxIndex;
  }

  /* ------------------------------------------------------------ autoplay */

  #startTimer() {
    this.#stopTimer();
    if (!this.autoplayDelay || REDUCED_MOTION.matches) return;
    if (this.paused || document.hidden || !this.visible) return;
    if (this.count <= this.perView) return;
    this.timer = setInterval(() => this.go(this.index + 1), this.autoplayDelay);
  }

  #stopTimer() {
    clearInterval(this.timer);
    this.timer = null;
  }

  #restartTimer() {
    if (this.timer) this.#startTimer();
  }
}

export function initSliders(scope = document) {
  return [...scope.querySelectorAll('[data-slider]')].map((el) => new Slider(el));
}
