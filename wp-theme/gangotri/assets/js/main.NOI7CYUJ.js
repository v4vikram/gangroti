// src/js/slider.js
var REDUCED_MOTION = window.matchMedia("(prefers-reduced-motion: reduce)");
var Slider = class {
  constructor(root) {
    this.root = root;
    this.viewport = root.querySelector(".slider-viewport");
    this.track = root.querySelector(".slider-track");
    this.prevBtn = root.querySelector("[data-slider-prev]");
    this.nextBtn = root.querySelector("[data-slider-next]");
    this.dotsBox = root.querySelector("[data-slider-dots]");
    if (!this.viewport || !this.track) return;
    this.originals = [...this.track.children];
    this.count = this.originals.length;
    if (!this.count) return;
    this.gap = Number(root.dataset.gap ?? 24);
    this.loop = root.hasAttribute("data-loop") && this.count > 1;
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
    this.root.setAttribute("role", "region");
    this.root.setAttribute("aria-roledescription", "carousel");
    if (!this.root.hasAttribute("aria-label")) {
      this.root.setAttribute("aria-label", "Slider");
    }
    this.viewport.setAttribute("aria-live", this.autoplayDelay ? "off" : "polite");
    this.originals.forEach((slide, i) => {
      slide.setAttribute("role", "group");
      slide.setAttribute("aria-roledescription", "slide");
      slide.setAttribute("aria-label", `${i + 1} of ${this.count}`);
    });
  }
  #bindEvents() {
    this.prevBtn?.addEventListener("click", () => this.go(this.index - 1, true));
    this.nextBtn?.addEventListener("click", () => this.go(this.index + 1, true));
    this.root.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") {
        e.preventDefault();
        this.go(this.index - 1, true);
      }
      if (e.key === "ArrowRight") {
        e.preventDefault();
        this.go(this.index + 1, true);
      }
    });
    this.track.addEventListener("transitionend", (e) => {
      if (e.target === this.track && e.propertyName === "transform") this.#onSettle();
    });
    for (const evt of ["pointerenter", "focusin"]) {
      this.root.addEventListener(evt, () => {
        this.paused = true;
        this.#stopTimer();
      });
    }
    for (const evt of ["pointerleave", "focusout"]) {
      this.root.addEventListener(evt, () => {
        this.paused = false;
        this.#startTimer();
      });
    }
    document.addEventListener("visibilitychange", () => {
      document.hidden ? this.#stopTimer() : this.#startTimer();
    });
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
    this.track.querySelectorAll("[data-clone]").forEach((n2) => n2.remove());
    this.cloneCount = 0;
    if (!this.loop) return;
    const n = Math.min(this.perView, this.count);
    const clone = (node) => {
      const c = node.cloneNode(true);
      c.setAttribute("data-clone", "");
      c.setAttribute("aria-hidden", "true");
      c.removeAttribute("role");
      c.querySelectorAll("a,button,input,select,textarea").forEach((el) => {
        el.setAttribute("tabindex", "-1");
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
    this.track.style.transition = animate && !REDUCED_MOTION.matches ? "transform 0.45s cubic-bezier(0.22, 1, 0.36, 1)" : "none";
    this.track.style.transform = `translate3d(${this.#offsetFor(this.index)}px, 0, 0)`;
  }
  go(target, fromUser = false) {
    if (this.animating) return;
    if (this.loop) {
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
      if (e.button !== void 0 && e.button !== 0) return;
      if (e.target.closest("a, button")) return;
      dragging = true;
      pointerId = e.pointerId;
      startX = e.clientX;
      startOffset = this.#offsetFor(this.index);
      this.track.style.transition = "none";
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
    this.viewport.addEventListener("pointerdown", onDown);
    this.viewport.addEventListener("pointermove", onMove);
    this.viewport.addEventListener("pointerup", onUp);
    this.viewport.addEventListener("pointercancel", onUp);
    this.viewport.style.touchAction = "pan-y";
    this.viewport.addEventListener("dragstart", (e) => e.preventDefault());
  }
  /* ----------------------------------------------------------- ui state */
  #renderDots(rebuild) {
    if (!this.dotsBox) return;
    const pages = this.loop ? this.count : this.maxIndex + 1;
    if (!rebuild && this.dotsBox.children.length === pages) return this.#syncDots();
    this.dotsBox.replaceChildren();
    this.dotsBox.setAttribute("role", "tablist");
    for (let i = 0; i < pages; i++) {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = "slider-dot";
      dot.setAttribute("role", "tab");
      dot.setAttribute("aria-label", `Go to slide ${i + 1}`);
      dot.addEventListener("click", () => this.go(i, true));
      this.dotsBox.append(dot);
    }
    this.#syncDots();
  }
  #syncDots() {
    if (!this.dotsBox) return;
    const active = (this.index + this.count) % this.count;
    [...this.dotsBox.children].forEach((dot, i) => {
      const on = i === active;
      dot.setAttribute("aria-selected", String(on));
      dot.classList.toggle("is-active", on);
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
};
function initSliders(scope = document) {
  return [...scope.querySelectorAll("[data-slider]")].map((el) => new Slider(el));
}

// src/js/filter.js
var PARAMS = ["destination", "duration", "month", "type", "sort"];
function matches(card, state) {
  const { destination, duration, type } = state;
  if (destination && card.dataset.destination !== destination) return false;
  if (type && card.dataset.type !== type) return false;
  if (duration) {
    const [min, max] = duration.split("-").map(Number);
    const days = Number(card.dataset.days);
    if (days < min || days > max) return false;
  }
  return true;
}
function sortCards(cards, mode) {
  const by = {
    "price-asc": (a, b) => a.dataset.price - b.dataset.price,
    "price-desc": (a, b) => b.dataset.price - a.dataset.price,
    "duration-asc": (a, b) => a.dataset.days - b.dataset.days,
    "duration-desc": (a, b) => b.dataset.days - a.dataset.days
  }[mode];
  return by ? [...cards].sort(by) : [...cards];
}
function initFilter() {
  const root = document.querySelector("[data-filter]");
  if (!root) return;
  const grid = document.querySelector("[data-filter-grid]");
  const empty = document.querySelector("[data-filter-empty]");
  const count = document.querySelector("[data-filter-count]");
  const reset = document.querySelector("[data-filter-reset]");
  const cards = [...grid.querySelectorAll("[data-yatra]")];
  const controls = PARAMS.map((name) => root.querySelector(`[name="${name}"]`)).filter(Boolean);
  function read() {
    return Object.fromEntries(controls.map((el) => [el.name, el.value]));
  }
  function apply({ push = true } = {}) {
    const state = read();
    let shown = 0;
    for (const card of cards) {
      const ok = matches(card, state);
      card.hidden = !ok;
      if (ok) shown++;
    }
    for (const card of sortCards(cards, state.sort)) grid.append(card);
    if (empty) empty.hidden = shown > 0;
    if (count) {
      count.textContent = shown === cards.length ? `${cards.length} yatras` : `${shown} of ${cards.length} yatras`;
    }
    if (!push) return;
    const url = new URL(location.href);
    for (const [key, value] of Object.entries(state)) {
      if (value) url.searchParams.set(key, value);
      else url.searchParams.delete(key);
    }
    history.replaceState(null, "", url);
  }
  const incoming = new URLSearchParams(location.search);
  for (const el of controls) {
    const value = incoming.get(el.name);
    if (value && [...el.options].some((o) => o.value === value)) el.value = value;
  }
  for (const el of controls) el.addEventListener("change", () => apply());
  reset?.addEventListener("click", () => {
    for (const el of controls) el.value = "";
    apply();
  });
  root.addEventListener("submit", (e) => {
    e.preventDefault();
    apply();
  });
  apply({ push: false });
}

// src/js/gallery.js
function initTabs() {
  const tabs = document.querySelector("[data-gallery-tabs]");
  const grid = document.querySelector("[data-gallery-grid]");
  if (!tabs || !grid) return;
  const buttons = [...tabs.querySelectorAll("[data-gallery-filter]")];
  const items = [...grid.children];
  tabs.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-gallery-filter]");
    if (!btn) return;
    const cat = btn.dataset.galleryFilter;
    for (const b of buttons) {
      const active = b === btn;
      b.classList.toggle("is-active", active);
      b.setAttribute("aria-selected", String(active));
    }
    for (const li of items) {
      li.hidden = cat !== "all" && li.dataset.cat !== cat;
    }
  });
}
function initLightbox() {
  const dialog = document.querySelector("[data-lightbox-dialog]");
  if (!dialog || typeof dialog.showModal !== "function") return;
  const img = dialog.querySelector("[data-lightbox-img]");
  const caption = dialog.querySelector("[data-lightbox-caption]");
  const triggers = [...document.querySelectorAll("[data-lightbox]")];
  if (!triggers.length) return;
  let index = 0;
  function show(i) {
    const visible = triggers.filter((t) => !t.closest("[hidden]"));
    if (!visible.length) return;
    index = (i + visible.length) % visible.length;
    const trigger = visible[index];
    img.src = trigger.dataset.full;
    img.alt = trigger.querySelector("img")?.alt ?? "";
    caption.textContent = trigger.dataset.caption ?? "";
  }
  for (const [i, trigger] of triggers.entries()) {
    trigger.addEventListener("click", () => {
      const visible = triggers.filter((t) => !t.closest("[hidden]"));
      show(visible.indexOf(trigger) === -1 ? i : visible.indexOf(trigger));
      dialog.showModal();
    });
  }
  dialog.querySelector("[data-lightbox-prev]")?.addEventListener("click", () => show(index - 1));
  dialog.querySelector("[data-lightbox-next]")?.addEventListener("click", () => show(index + 1));
  dialog.querySelector("[data-lightbox-close]")?.addEventListener("click", () => dialog.close());
  dialog.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") show(index - 1);
    if (e.key === "ArrowRight") show(index + 1);
  });
  dialog.addEventListener("click", (e) => {
    if (e.target === dialog) dialog.close();
  });
  dialog.addEventListener("close", () => {
    img.removeAttribute("src");
  });
}
function initGallery() {
  initTabs();
  initLightbox();
}

// src/js/form.js
var REQUIRED = ["name", "phone"];
function setError(input, message) {
  const field = input.closest("div");
  let note = field.querySelector(".field-error");
  if (!message) {
    input.removeAttribute("aria-invalid");
    note?.remove();
    return false;
  }
  input.setAttribute("aria-invalid", "true");
  if (!note) {
    note = document.createElement("p");
    note.className = "field-error";
    field.append(note);
  }
  note.textContent = message;
  return true;
}
function validate(form) {
  let firstBad = null;
  for (const name of REQUIRED) {
    const input = form.elements[name];
    const bad = setError(input, input.value.trim() ? "" : "This one we do need.");
    if (bad && !firstBad) firstBad = input;
  }
  const email = form.elements.email;
  if (email.value.trim() && !email.checkValidity()) {
    const bad = setError(email, "That email address does not look right.");
    if (bad && !firstBad) firstBad = email;
  } else {
    setError(email, "");
  }
  const phone = form.elements.phone;
  if (phone.value.trim() && !phone.checkValidity()) {
    const bad = setError(phone, "Please include a full phone number.");
    if (bad && !firstBad) firstBad = phone;
  }
  firstBad?.focus();
  return !firstBad;
}
var WP = globalThis.gangotriData;
var ENDPOINT = WP?.endpoint ?? "";
function setStatus(status, state, message) {
  status.hidden = false;
  status.className = `sm:col-span-2 form-status is-${state}`;
  status.textContent = message;
}
async function send(form) {
  const body = new FormData(form);
  body.append("action", "ge_enquiry");
  if (WP?.nonce) body.append("nonce", WP.nonce);
  body.append("source", location.href);
  const res = await fetch(ENDPOINT, {
    method: "POST",
    body,
    headers: { "X-Requested-With": "XMLHttpRequest" }
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const data = await res.json().catch(() => ({ success: res.ok }));
  if (data.success === false) throw new Error(data.data?.message ?? "Rejected");
  return data;
}
function initEnquiryForm(form) {
  const status = form.querySelector("[data-form-status]");
  const submit = form.querySelector('button[type="submit"]');
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (form.elements.company.value) {
      setStatus(status, "ok", "Thank you - we will be in touch.");
      return;
    }
    if (!validate(form)) return;
    submit.disabled = true;
    setStatus(status, "pending", "Sending your enquiry...");
    if (!ENDPOINT) {
      submit.disabled = false;
      setStatus(
        status,
        "pending",
        "Form is not connected yet - this gets wired up when the site moves to WordPress."
      );
      return;
    }
    try {
      await send(form);
      form.reset();
      markShown();
      setStatus(status, "ok", "Thank you. We have your details and will call you shortly.");
    } catch {
      setStatus(
        status,
        "error",
        "Something went wrong sending that. Please call or WhatsApp us instead."
      );
    } finally {
      submit.disabled = false;
    }
  });
  for (const el of form.elements) {
    el.addEventListener("input", () => {
      if (el.hasAttribute("aria-invalid")) setError(el, "");
    });
  }
}
function initMap() {
  const box = document.querySelector("[data-map]");
  if (!box) return;
  box.querySelector("[data-map-load]")?.addEventListener("click", () => {
    const iframe = document.createElement("iframe");
    iframe.src = box.dataset.src;
    iframe.title = "Our location on Google Maps";
    iframe.loading = "lazy";
    iframe.referrerPolicy = "no-referrer-when-downgrade";
    iframe.allowFullscreen = true;
    box.replaceChildren(iframe);
  });
}
var AUTO = {
  delay: 25e3,
  // ms on the page
  scroll: 0.45,
  // or this much of the page read, whichever comes first
  key: "ge-enquiry-seen"
};
function autoSettings(modal) {
  return {
    delay: Number(modal.dataset.autoDelay) || AUTO.delay,
    scroll: Number(modal.dataset.autoScroll) || AUTO.scroll
  };
}
function alreadyShown() {
  try {
    return sessionStorage.getItem(AUTO.key) === "1";
  } catch {
    return true;
  }
}
function markShown() {
  try {
    sessionStorage.setItem(AUTO.key, "1");
  } catch {
  }
}
function initEnquiryModal() {
  const modal = document.querySelector("[data-enquiry-modal]");
  if (!modal || typeof modal.showModal !== "function") return;
  function open({ auto = false } = {}) {
    if (modal.open) return;
    if (auto && alreadyShown()) return;
    markShown();
    modal.showModal();
    modal.querySelector('input[name="name"]')?.focus();
  }
  for (const trigger of document.querySelectorAll("[data-enquiry-open]")) {
    trigger.addEventListener("click", () => open());
  }
  modal.querySelector("[data-enquiry-close]")?.addEventListener("click", () => modal.close());
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.close();
  });
  if (modal.hasAttribute("data-auto-off")) return;
  if (alreadyShown()) return;
  const inlineForm = document.querySelector("#enquiry");
  let inlineVisible = false;
  if (inlineForm) {
    new IntersectionObserver(([entry]) => {
      inlineVisible = entry.isIntersecting;
    }).observe(inlineForm);
  }
  const { delay, scroll } = autoSettings(modal);
  const timer = setTimeout(tryOpen, delay);
  function tryOpen() {
    clearTimeout(timer);
    removeEventListener("scroll", onScroll);
    if (inlineVisible) return;
    open({ auto: true });
  }
  function onScroll() {
    const read = scrollY / Math.max(1, document.body.scrollHeight - innerHeight);
    if (read >= scroll) tryOpen();
  }
  addEventListener("scroll", onScroll, { passive: true });
}
function initForms() {
  for (const form of document.querySelectorAll("[data-enquiry-form]")) initEnquiryForm(form);
  initEnquiryModal();
  initMap();
}

// src/js/main.js
function initHeader() {
  const header = document.querySelector("[data-header]");
  if (!header) return;
  let ticking = false;
  const update = () => {
    header.classList.toggle("is-scrolled", window.scrollY > 24);
    ticking = false;
  };
  addEventListener("scroll", () => {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(update);
    }
  }, { passive: true });
  update();
}
function initMobileNav() {
  const toggle = document.querySelector("[data-nav-toggle]");
  const panel = document.querySelector("[data-nav-panel]");
  if (!toggle || !panel) return;
  const FOCUSABLE = "a[href], button:not([disabled]), input, select, textarea";
  let lastFocused = null;
  const open = () => {
    lastFocused = document.activeElement;
    panel.hidden = false;
    requestAnimationFrame(() => panel.classList.add("is-open"));
    toggle.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    panel.querySelector(FOCUSABLE)?.focus();
  };
  const close = () => {
    panel.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
    lastFocused?.focus();
    const done = () => {
      panel.hidden = true;
    };
    panel.addEventListener("transitionend", done, { once: true });
    setTimeout(done, 400);
  };
  toggle.addEventListener("click", () => {
    toggle.getAttribute("aria-expanded") === "true" ? close() : open();
  });
  panel.addEventListener("click", (e) => {
    if (e.target.closest("a, [data-nav-close]")) close();
  });
  document.addEventListener("keydown", (e) => {
    if (panel.hidden) return;
    if (e.key === "Escape") return close();
    if (e.key !== "Tab") return;
    const items = [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent);
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });
}
function initAccordions() {
  for (const acc of document.querySelectorAll("[data-accordion]")) {
    const single = acc.hasAttribute("data-accordion-single");
    const triggers = acc.querySelectorAll("[data-accordion-trigger]");
    for (const trigger of triggers) {
      const panel = document.getElementById(trigger.getAttribute("aria-controls"));
      if (!panel) continue;
      trigger.addEventListener("click", () => {
        const isOpen = trigger.getAttribute("aria-expanded") === "true";
        if (single && !isOpen) {
          for (const other of triggers) {
            if (other === trigger) continue;
            other.setAttribute("aria-expanded", "false");
            const p = document.getElementById(other.getAttribute("aria-controls"));
            if (p) p.style.height = "0px";
          }
        }
        trigger.setAttribute("aria-expanded", String(!isOpen));
        panel.style.height = isOpen ? `${panel.scrollHeight}px` : "0px";
        requestAnimationFrame(() => {
          panel.style.height = isOpen ? "0px" : `${panel.scrollHeight}px`;
        });
      });
      panel.addEventListener("transitionend", (e) => {
        if (e.propertyName !== "height") return;
        if (trigger.getAttribute("aria-expanded") === "true") panel.style.height = "auto";
      });
    }
  }
}
function initReveal() {
  const items = document.querySelectorAll("[data-reveal]");
  if (!items.length) return;
  if (matchMedia("(prefers-reduced-motion: reduce)").matches) {
    items.forEach((el) => el.classList.add("is-revealed"));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (!entry.isIntersecting) continue;
      entry.target.classList.add("is-revealed");
      io.unobserve(entry.target);
    }
  }, { rootMargin: "0px 0px -10% 0px", threshold: 0.1 });
  items.forEach((el) => io.observe(el));
}
function initYear() {
  const year = String((/* @__PURE__ */ new Date()).getFullYear());
  document.querySelectorAll("[data-year]").forEach((el) => {
    el.textContent = year;
  });
}
initHeader();
initMobileNav();
initAccordions();
initReveal();
initSliders();
initFilter();
initGallery();
initForms();
initYear();
