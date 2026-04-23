/* ============================================================
   Schlägerbesaitung André Bachmann — script.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // ── HERO: BEAMS CANVAS ───────────────────────────────────
  (function initBeams() {
    const canvas = document.getElementById('heroCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const BEAM_COUNT = 30;
    let beams = [];
    let rafId;

    function createBeam(w, h) {
      const angle = -35 + Math.random() * 10;
      return {
        x:         Math.random() * w * 1.5 - w * 0.25,
        y:         Math.random() * h * 1.5 - h * 0.25,
        width:     30 + Math.random() * 60,
        length:    h * 2.5,
        angle,
        speed:     0.6 + Math.random() * 1.2,
        opacity:   0.12 + Math.random() * 0.16,
        hue:       190 + Math.random() * 70,
        pulse:     Math.random() * Math.PI * 2,
        pulseSpeed:0.02 + Math.random() * 0.03,
      };
    }

    function resetBeam(beam, index, total, w, h) {
      const col     = index % 3;
      const spacing = w / 3;
      beam.y         = h + 100;
      beam.x         = col * spacing + spacing / 2 + (Math.random() - 0.5) * spacing * 0.5;
      beam.width     = 100 + Math.random() * 100;
      beam.speed     = 0.5 + Math.random() * 0.4;
      beam.hue       = 190 + (index * 70) / total;
      beam.opacity   = 0.2 + Math.random() * 0.1;
    }

    function resize() {
      const dpr = window.devicePixelRatio || 1;
      const w   = window.innerWidth;
      const h   = window.innerHeight;
      canvas.width  = w * dpr;
      canvas.height = h * dpr;
      canvas.style.width  = w + 'px';
      canvas.style.height = h + 'px';
      ctx.scale(dpr, dpr);
      beams = Array.from({ length: BEAM_COUNT }, () => createBeam(w, h));
    }

    function draw() {
      const w = window.innerWidth;
      const h = window.innerHeight;
      ctx.clearRect(0, 0, w, h);
      ctx.filter = 'blur(35px)';

      beams.forEach((beam, i) => {
        beam.y     -= beam.speed;
        beam.pulse += beam.pulseSpeed;
        if (beam.y + beam.length < -100) resetBeam(beam, i, beams.length, w, h);

        const op = beam.opacity * (0.8 + Math.sin(beam.pulse) * 0.2);
        ctx.save();
        ctx.translate(beam.x, beam.y);
        ctx.rotate(beam.angle * Math.PI / 180);

        const grad = ctx.createLinearGradient(0, 0, 0, beam.length);
        grad.addColorStop(0,   `hsla(${beam.hue},85%,65%,0)`);
        grad.addColorStop(0.1, `hsla(${beam.hue},85%,65%,${op * 0.5})`);
        grad.addColorStop(0.4, `hsla(${beam.hue},85%,65%,${op})`);
        grad.addColorStop(0.6, `hsla(${beam.hue},85%,65%,${op})`);
        grad.addColorStop(0.9, `hsla(${beam.hue},85%,65%,${op * 0.5})`);
        grad.addColorStop(1,   `hsla(${beam.hue},85%,65%,0)`);

        ctx.fillStyle = grad;
        ctx.fillRect(-beam.width / 2, 0, beam.width, beam.length);
        ctx.restore();
      });

      rafId = requestAnimationFrame(draw);
    }

    resize();
    window.addEventListener('resize', resize);
    draw();

    // Stop animation when tab is hidden (performance)
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) { cancelAnimationFrame(rafId); }
      else { draw(); }
    });
  })();

  // ── FAQ: SPIRAL BACKGROUND ──────────────────────────────
  (function initFaqSpiral() {
    const svg = document.getElementById('faqSpiral');
    if (!svg) return;

    const svgNS = 'http://www.w3.org/2000/svg';
    const SIZE  = 560;
    const N     = 700;
    const DOT   = 1.8;
    const CENTER = SIZE / 2;
    const MAX_R  = CENTER - 4 - DOT;
    const GOLDEN = Math.PI * (3 - Math.sqrt(5));

    for (let i = 0; i < N; i++) {
      const frac  = (i + 0.5) / N;
      const r     = Math.sqrt(frac) * MAX_R;
      const theta = (i + 0.5) * GOLDEN;
      const x     = CENTER + r * Math.cos(theta);
      const y     = CENTER + r * Math.sin(theta);

      const c = document.createElementNS(svgNS, 'circle');
      c.setAttribute('cx', x.toFixed(2));
      c.setAttribute('cy', y.toFixed(2));
      c.setAttribute('r',  String(DOT));
      c.setAttribute('fill', '#ffffff');
      c.setAttribute('opacity', '0.6');

      // Pulse size
      const animR = document.createElementNS(svgNS, 'animate');
      animR.setAttribute('attributeName', 'r');
      animR.setAttribute('values', `${DOT * 0.5};${DOT * 1.4};${DOT * 0.5}`);
      animR.setAttribute('dur',   '3s');
      animR.setAttribute('begin', `${(frac * 3).toFixed(3)}s`);
      animR.setAttribute('repeatCount', 'indefinite');
      animR.setAttribute('calcMode', 'spline');
      animR.setAttribute('keySplines', '0.4 0 0.6 1;0.4 0 0.6 1');
      c.appendChild(animR);

      // Pulse opacity
      const animO = document.createElementNS(svgNS, 'animate');
      animO.setAttribute('attributeName', 'opacity');
      animO.setAttribute('values', '0.25;0.9;0.25');
      animO.setAttribute('dur',   '3s');
      animO.setAttribute('begin', `${(frac * 3).toFixed(3)}s`);
      animO.setAttribute('repeatCount', 'indefinite');
      animO.setAttribute('calcMode', 'spline');
      animO.setAttribute('keySplines', '0.4 0 0.6 1;0.4 0 0.6 1');
      c.appendChild(animO);

      svg.appendChild(c);
    }
  })();

  // ── FAQ: ACCORDION ───────────────────────────────────────
  (function initFaqAccordion() {
    document.querySelectorAll('.faq-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const card    = btn.closest('.faq-card');
        const body    = card.querySelector('.faq-body');
        const isOpen  = btn.getAttribute('aria-expanded') === 'true';

        // Close all
        document.querySelectorAll('.faq-btn').forEach(b => {
          b.setAttribute('aria-expanded', 'false');
          b.closest('.faq-card').querySelector('.faq-body').classList.remove('open');
        });

        // Open clicked (toggle)
        if (!isOpen) {
          btn.setAttribute('aria-expanded', 'true');
          body.classList.add('open');
        }
      });
    });

    // Wrap faq-body content in inner div for grid-template-rows trick
    document.querySelectorAll('.faq-body').forEach(body => {
      const inner = document.createElement('div');
      while (body.firstChild) inner.appendChild(body.firstChild);
      body.appendChild(inner);
    });
  })();

  // ── HERO: CONTENT FADE-IN ────────────────────────────────
  (function initHeroContent() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const items = [
      { el: document.getElementById('heroBadge'),   delay: 500  },
      { el: document.getElementById('heroTitle'),   delay: 700  },
      { el: document.getElementById('heroSubtitle'),delay: 1000 },
      { el: document.getElementById('heroActions'), delay: 1300 },
    ];

    items.forEach(({ el, delay }) => {
      if (!el) return;
      if (reducedMotion) { el.style.opacity = '1'; return; }

      setTimeout(() => {
        el.style.transition = 'opacity 0.9s ease, transform 0.9s ease';
        el.style.transform  = 'translateY(0)';
        el.style.opacity    = '1';
      }, delay);

      // Set initial transform so animation is smooth
      el.style.transform = 'translateY(28px)';
    });
  })();

  // ── A: SCROLL PROGRESS BAR ───────────────────────────────
  const progressBar = document.getElementById('progress-bar');

  function updateProgress() {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
    progressBar.style.width = progress + '%';
  }


  // ── B: NAVBAR SCROLL EFFECT ──────────────────────────────
  const navbar = document.getElementById('navbar');

  function updateNavbar() {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  }


  // ── C: COMBINED SCROLL HANDLER ───────────────────────────
  window.addEventListener('scroll', () => {
    updateProgress();
    updateNavbar();
  }, { passive: true });

  // Run on load in case page is refreshed mid-scroll
  updateProgress();
  updateNavbar();


  // ── D: MOBILE MENU ───────────────────────────────────────
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  function closeMobileMenu() {
    navMenu.classList.remove('open');
    navToggle.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
    navToggle.setAttribute('aria-label', 'Menü öffnen');
  }

  navToggle.addEventListener('click', () => {
    const isOpen = navMenu.classList.contains('open');
    if (isOpen) {
      closeMobileMenu();
    } else {
      navMenu.classList.add('open');
      navToggle.classList.add('open');
      navToggle.setAttribute('aria-expanded', 'true');
      navToggle.setAttribute('aria-label', 'Menü schließen');
    }
  });

  // Close menu when a nav link is clicked
  navMenu.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', closeMobileMenu);
  });

  // Close menu on outside click
  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) {
      closeMobileMenu();
    }
  });


  // ── E: SMOOTH SCROLL WITH OFFSET ─────────────────────────
  const NAV_HEIGHT = 80;

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const href = anchor.getAttribute('href');
      if (href === '#') return;
      const target = document.querySelector(href);
      if (!target) return;

      e.preventDefault();
      const top = target.getBoundingClientRect().top + window.scrollY - NAV_HEIGHT;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });


  // ── F: ACTIVE NAV LINK (IntersectionObserver) ────────────
  const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id');
        navLinks.forEach(link => {
          const matches = link.getAttribute('href') === `#${id}`;
          link.classList.toggle('active', matches);
        });
      }
    });
  }, {
    threshold: 0.35,
    rootMargin: `-${NAV_HEIGHT}px 0px 0px 0px`
  });

  document.querySelectorAll('section[id]').forEach(section => {
    sectionObserver.observe(section);
  });


  // ── G: SCROLL REVEAL ANIMATIONS ──────────────────────────
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.12,
    rootMargin: '0px 0px -40px 0px'
  });

  document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
    revealObserver.observe(el);
  });


  // ── H: CONTACT FORM ──────────────────────────────────────
  const contactForm = document.getElementById('contactForm');
  const formSuccess = document.getElementById('formSuccess');
  const submitBtn = document.getElementById('submitBtn');

  if (contactForm) {

    function getField(id) {
      return document.getElementById(id);
    }

    function getError(id) {
      return document.getElementById(id + '-error');
    }

    function showError(id, message) {
      const field = getField(id);
      const error = getError(id);
      if (field) field.classList.add('error');
      if (error) {
        error.textContent = message || error.textContent;
        error.classList.add('visible');
      }
    }

    function clearError(id) {
      const field = getField(id);
      const error = getError(id);
      if (field) field.classList.remove('error');
      if (error) error.classList.remove('visible');
    }

    function isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validateField(fieldId) {
      const field = getField(fieldId);
      if (!field) return true;

      const value = field.value.trim();

      if (fieldId === 'name') {
        if (!value) { showError('name', 'Bitte gib deinen Namen ein.'); return false; }
        clearError('name'); return true;
      }
      if (fieldId === 'email') {
        if (!value) { showError('email', 'Bitte gib deine E-Mail-Adresse ein.'); return false; }
        if (!isValidEmail(value)) { showError('email', 'Bitte gib eine gültige E-Mail-Adresse ein.'); return false; }
        clearError('email'); return true;
      }
      if (fieldId === 'nachricht') {
        if (!value) { showError('nachricht', 'Bitte gib eine Nachricht ein.'); return false; }
        clearError('nachricht'); return true;
      }
      return true;
    }

    // Real-time validation on blur
    ['name', 'email', 'nachricht'].forEach(id => {
      const field = getField(id);
      if (field) {
        field.addEventListener('blur', () => validateField(id));
        field.addEventListener('input', () => clearError(id));
      }
    });

    contactForm.addEventListener('submit', (e) => {
      e.preventDefault();

      // Honeypot check
      const honeypot = contactForm.querySelector('input[name="website"]');
      if (honeypot && honeypot.value) return;

      // Validate all required fields
      const validName    = validateField('name');
      const validEmail   = validateField('email');
      const validMessage = validateField('nachricht');

      if (!validName || !validEmail || !validMessage) return;

      // Show loading state
      submitBtn.disabled = true;
      submitBtn.textContent = 'Wird gesendet…';

      // Simulate async submission (replace with fetch() when backend ready)
      setTimeout(() => {
        contactForm.style.display = 'none';
        formSuccess.classList.add('visible');
        contactForm.reset();
      }, 900);
    });
  }


}); // end DOMContentLoaded
