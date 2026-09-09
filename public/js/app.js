/* ==========================================================================
   MediMax — front-end behaviour
   Presentation only: nothing here posts, persists, or mutates server state.
   ========================================================================== */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- Toasts ------------------------------------------------------------ */
  function toastStack() {
    var stack = document.querySelector('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      stack.setAttribute('aria-live', 'polite');
      document.body.appendChild(stack);
    }
    return stack;
  }

  function dismissToast(el) {
    el.classList.add('is-leaving');
    setTimeout(function () { el.remove(); }, 260);
  }

  function toast(text, icon) {
    var el = document.createElement('div');
    el.className = 'toast-msg';
    el.setAttribute('role', 'status');
    el.innerHTML = '<i class="fas ' + (icon || 'fa-circle-check') + '" aria-hidden="true"></i><span></span>';
    el.querySelector('span').textContent = text;
    el.addEventListener('click', function () { dismissToast(el); });
    toastStack().appendChild(el);
    setTimeout(function () {
      if (el.isConnected) dismissToast(el);
    }, 2600);
  }

  document.addEventListener('DOMContentLoaded', function () {

    /* --- Server-rendered toasts: auto-dismiss -------------------------- */
    document.querySelectorAll('.toast-msg').forEach(function (el) {
      el.addEventListener('click', function () { dismissToast(el); });
      setTimeout(function () { if (el.isConnected) dismissToast(el); }, 3200);
    });

    /* --- Navbar: condense once the page scrolls ------------------------ */
    var nav = document.querySelector('.mm-nav');
    var toTop = document.querySelector('.to-top');

    function onScroll() {
      var y = window.scrollY;
      if (nav) nav.classList.toggle('is-stuck', y > 12);
      if (toTop) toTop.classList.toggle('is-visible', y > 520);
    }
    if (nav || toTop) {
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }
    if (toTop) {
      toTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      });
    }

    /* --- Scroll reveal ------------------------------------------------- */
    var revealables = document.querySelectorAll('.reveal');
    if (revealables.length) {
      if (reduceMotion || !('IntersectionObserver' in window)) {
        revealables.forEach(function (el) { el.classList.add('is-in'); });
      } else {
        var io = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-in');
              io.unobserve(entry.target);
            }
          });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
        revealables.forEach(function (el) { io.observe(el); });
      }
    }

    /* --- Account menu -------------------------------------------------- */
    var accountBtn = document.getElementById('profileOptionsBtn');
    var accountMenu = document.getElementById('profileOptions');
    if (accountBtn && accountMenu) {
      var closeMenu = function () {
        accountMenu.classList.add('d-none');
        accountBtn.setAttribute('aria-expanded', 'false');
      };
      accountBtn.setAttribute('aria-expanded', 'false');
      accountBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var willOpen = accountMenu.classList.contains('d-none');
        accountMenu.classList.toggle('d-none', !willOpen);
        accountBtn.setAttribute('aria-expanded', String(willOpen));
      });
      document.addEventListener('click', function (e) {
        if (!accountMenu.contains(e.target) && !accountBtn.contains(e.target)) closeMenu();
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
      });
      window.addEventListener('scroll', function () {
        if (!accountMenu.classList.contains('d-none')) closeMenu();
      }, { passive: true });
    }

    /* --- Search: focus with "/" --------------------------------------- */
    var search = document.querySelector('.mm-search input');
    if (search) {
      document.addEventListener('keydown', function (e) {
        var typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName);
        if (e.key === '/' && !typing && !e.metaKey && !e.ctrlKey) {
          e.preventDefault();
          search.focus();
        }
      });
    }

    /* --- Avatar tint: derived from the name, so it stays put ----------- */
    var palette = ['#26547C', '#2C7A80', '#B4342B', '#8A5A2B', '#5B4A9E'];
    document.querySelectorAll('[data-avatar-tint]').forEach(function (el) {
      var seed = (el.getAttribute('data-avatar-tint') || el.textContent || '').trim();
      var sum = 0;
      for (var i = 0; i < seed.length; i++) sum += seed.charCodeAt(i);
      el.style.backgroundColor = palette[sum % palette.length];
    });

    /* --- Quantity steppers -------------------------------------------- */
    document.querySelectorAll('.stepper').forEach(function (stepper) {
      var input = stepper.querySelector('input');
      if (!input) return;
      stepper.querySelectorAll('button[data-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var step = parseInt(btn.getAttribute('data-step'), 10) || 1;
          var min = parseInt(input.getAttribute('min'), 10);
          var next = (parseInt(input.value, 10) || 1) + step;
          if (!isNaN(min) && next < min) next = min;
          input.value = next;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    });

    /* --- Product card actions (visual feedback for the prototype) ------ */
    document.querySelectorAll('[data-act="cart"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var name = btn.getAttribute('data-name') || 'Item';
        btn.classList.add('is-done');
        btn.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i>';
        toast(name + ' added to cart', 'fa-cart-plus');
        setTimeout(function () {
          btn.classList.remove('is-done');
          btn.innerHTML = '<i class="fas fa-cart-plus" aria-hidden="true"></i>';
        }, 1600);
      });
    });

    document.querySelectorAll('[data-act="fav"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var name = btn.getAttribute('data-name') || 'Item';
        var on = btn.classList.toggle('is-fav');
        btn.innerHTML = '<i class="' + (on ? 'fas' : 'far') + ' fa-heart" aria-hidden="true"></i>';
        btn.setAttribute('aria-pressed', String(on));
        toast(on ? name + ' saved to wishlist' : name + ' removed from wishlist', on ? 'fa-heart' : 'fa-heart-crack');
      });
    });

    /* --- Catalogue filter + sort (client side) ------------------------- */
    var grid = document.getElementById('catalogue');
    if (grid) {
      var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-category]'));
      var chips = Array.prototype.slice.call(document.querySelectorAll('[data-filter]'));
      var sortSelect = document.getElementById('catalogueSort');
      var countEl = document.getElementById('catalogueCount');
      var emptyEl = document.getElementById('catalogueEmpty');
      var activeFilter = 'all';

      var params = new URLSearchParams(window.location.search);
      var wanted = params.get('cat');
      if (wanted) {
        var match = chips.filter(function (c) {
          return c.getAttribute('data-filter').toLowerCase() === wanted.toLowerCase();
        })[0];
        if (match) activeFilter = match.getAttribute('data-filter');
      }

      function apply() {
        var shown = 0;
        cards.forEach(function (card) {
          var keep = activeFilter === 'all' || card.getAttribute('data-category') === activeFilter;
          card.hidden = !keep;
          if (keep) shown++;
        });
        chips.forEach(function (chip) {
          chip.classList.toggle('is-active', chip.getAttribute('data-filter') === activeFilter);
          chip.setAttribute('aria-pressed', String(chip.getAttribute('data-filter') === activeFilter));
        });
        if (countEl) countEl.textContent = shown;
        if (emptyEl) emptyEl.hidden = shown !== 0;
      }

      function sort(mode) {
        var sorted = cards.slice().sort(function (a, b) {
          var pa = parseFloat(a.getAttribute('data-price')) || 0;
          var pb = parseFloat(b.getAttribute('data-price')) || 0;
          var na = (a.getAttribute('data-name') || '').toLowerCase();
          var nb = (b.getAttribute('data-name') || '').toLowerCase();
          if (mode === 'price-asc') return pa - pb;
          if (mode === 'price-desc') return pb - pa;
          if (mode === 'name-asc') return na < nb ? -1 : na > nb ? 1 : 0;
          return (parseInt(a.getAttribute('data-index'), 10) || 0) - (parseInt(b.getAttribute('data-index'), 10) || 0);
        });
        sorted.forEach(function (card) { grid.appendChild(card); });
      }

      chips.forEach(function (chip) {
        chip.addEventListener('click', function (e) {
          e.preventDefault();
          activeFilter = chip.getAttribute('data-filter');
          apply();
        });
      });

      if (sortSelect) {
        sortSelect.addEventListener('change', function () { sort(sortSelect.value); });
      }

      apply();
    }

    /* --- Password visibility ------------------------------------------ */
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var field = document.getElementById(btn.getAttribute('data-pw-toggle'));
        if (!field) return;
        var show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        btn.innerHTML = '<i class="fas ' + (show ? 'fa-eye-slash' : 'fa-eye') + '" aria-hidden="true"></i>';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
    });

    /* --- Admin rail drawer -------------------------------------------- */
    var rail = document.querySelector('.admin-rail');
    var scrim = document.querySelector('.admin-scrim');
    var burger = document.querySelector('.admin-burger');
    if (rail && burger) {
      var setRail = function (open) {
        rail.classList.toggle('is-open', open);
        if (scrim) scrim.classList.toggle('is-open', open);
        burger.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
      };
      burger.addEventListener('click', function () { setRail(!rail.classList.contains('is-open')); });
      if (scrim) scrim.addEventListener('click', function () { setRail(false); });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setRail(false);
      });
    }

    /* --- Destructive actions: confirm before firing -------------------- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        if (!window.confirm(el.getAttribute('data-confirm'))) {
          e.preventDefault();
          e.stopPropagation();
        }
      });
    });

    /* --- Image fallback ----------------------------------------------- */
    document.querySelectorAll('img[data-fallback]').forEach(function (img) {
      img.addEventListener('error', function () {
        if (img.dataset.fallbackApplied) return;
        img.dataset.fallbackApplied = '1';
        img.src = img.getAttribute('data-fallback');
      });
    });

    /* --- Auth forms: check before the round trip ----------------------
       Front-of-house checks only. The server stays the authority; this
       just saves a page load and says what to fix, in the field. */
    var authForms = [
      {
        form: 'loginForm',
        checks: [
          { input: 'identifier', out: 'identifierError', test: function (v) {
              if (!v) return 'Enter your username or email.';
              return '';
            } },
          { input: 'password', out: 'passwordError', test: function (v) {
              if (!v) return 'Enter your password.';
              return '';
            } }
        ]
      },
      {
        form: 'registrationForm',
        checks: [
          { input: 'username', out: 'usernameError', test: function (v) {
              if (!v) return 'Enter your name.';
              if (v.length < 2) return 'That looks too short to be a name.';
              return '';
            } },
          { input: 'email', out: 'emailError', test: function (v) {
              if (!v) return 'Enter your email.';
              if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'That email is missing something — check the @ and the domain.';
              return '';
            } },
          { input: 'password', out: 'passwordError', test: function (v) {
              if (!v) return 'Choose a password.';
              if (v.length < 8) return 'Use at least 8 characters.';
              return '';
            } }
        ]
      }
    ];

    authForms.forEach(function (spec) {
      var form = document.getElementById(spec.form);
      if (!form) return;

      var fields = spec.checks.map(function (c) {
        return { input: document.getElementById(c.input), out: document.getElementById(c.out), test: c.test };
      }).filter(function (f) { return f.input && f.out; });

      var show = function (f) {
        var msg = f.test(f.input.value.trim());
        f.out.textContent = msg;
        f.input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        f.input.style.borderColor = msg ? 'var(--danger)' : '';
        return !msg;
      };

      fields.forEach(function (f) {
        f.input.addEventListener('blur', function () { show(f); });
        f.input.addEventListener('input', function () {
          if (f.out.textContent) show(f);
        });
      });

      form.addEventListener('submit', function (e) {
        var firstBad = null;
        fields.forEach(function (f) {
          if (!show(f) && !firstBad) firstBad = f.input;
        });
        if (firstBad) {
          e.preventDefault();
          firstBad.focus();
        }
      });
    });
  });

  window.MediMax = { toast: toast };
})();
