(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.addEventListener('DOMContentLoaded', function () {

    /* --- Navbar: condense once the page scrolls & back-to-top animation --- */
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

    /* --- Profile menu toggle ------------------------------------------- */
    var profileBtn = document.getElementById('profileOptionsBtn');
    var profileMenu = document.getElementById('profileOptions');

    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isHidden = profileMenu.classList.contains('d-none');
        profileMenu.classList.toggle('d-none');
        profileBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
      });

      document.addEventListener('click', function (e) {
        if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
          profileMenu.classList.add('d-none');
          profileBtn.setAttribute('aria-expanded', 'false');
        }
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

    /* --- Shared jQuery form validation ------------------------------- */
    if (window.jQuery) {
      (function ($) {
        function validateField(input) {
          var field = $(input);
          var value = field.val() ? String(field.val()).trim() : '';
          var errorSpan = $('#' + (field.attr('id') || field.attr('name')) + 'Error');
          if (!errorSpan.length) errorSpan = field.siblings('.validation-error').first();
          var validationType = field.data('validation') || '';
          var minLength = field.data('min') || 0;
          var maxLength = field.data('max') || 9999;
          var filesize = field.data('filesize') || 0;
          var fileTypes = String(field.data('filetypes') || '').split(',').filter(Boolean);
          var errorMessage = '';

          if (!validationType) return true;

          if (validationType.includes('required') && value === '') {
            errorMessage = 'This field is required.';
          }
          if (!errorMessage && value !== '' && validationType.includes('email') &&
              !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)) {
            errorMessage = 'Please enter a valid email address.';
          }
          if (!errorMessage && value !== '' && validationType.includes('strongPassword') &&
              !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,25}$/.test(value)) {
            errorMessage = 'Password must be at least 8 characters, include uppercase, lowercase, number, and special character.';
          }
          if (!errorMessage && validationType.includes('confirmPassword')) {
            var password = $('#' + field.data('password-id')).val().trim();
            if (value !== password) errorMessage = 'Passwords do not match.';
          }
          if (!errorMessage && validationType.includes('terms') && !field.is(':checked')) {
            errorMessage = 'You must agree to the Terms & Conditions.';
          }
          if (!errorMessage && value !== '' && validationType.includes('alpha') && !/^[A-Za-z\s]+$/.test(value)) {
            errorMessage = 'Only letters are allowed.';
          }
          if (!errorMessage && value !== '' && validationType.includes('numeric') && !/^[0-9]+$/.test(value)) {
            errorMessage = 'Only numbers are allowed.';
          }
          if (!errorMessage && value !== '' && validationType.includes('min') && value.length < minLength) {
            errorMessage = 'Must be at least ' + minLength + ' characters.';
          }
          if (!errorMessage && value !== '' && validationType.includes('max') && value.length > maxLength) {
            errorMessage = 'Must be less than ' + maxLength + ' characters.';
          }
          if (!errorMessage && validationType.includes('file')) {
            if (input.files && input.files.length > 0) {
              var file = input.files[0];
              var extension = file.name.split('.').pop().toLowerCase();
              if (fileTypes.length && fileTypes.indexOf(extension) === -1) {
                errorMessage = 'Only JPG, JPEG, or PNG files are allowed.';
              } else if (validationType.includes('filesize') && file.size / 1024 > filesize) {
                errorMessage = 'File size must be less than ' + filesize + ' KB.';
              }
            } else if (validationType.includes('required')) {
              errorMessage = 'Please upload a file.';
            }
          }
          if (!errorMessage && field.is('select') && validationType.includes('required') &&
              (value === '' || field.find('option:selected').index() === 0)) {
            errorMessage = 'Please select an option.';
          }

          if (errorMessage) {
            errorSpan.text(errorMessage).show();
            field.addClass('is-invalid').removeClass('is-valid').attr('aria-invalid', 'true');
          } else {
            errorSpan.text('').hide();
            field.removeClass('is-invalid').addClass('is-valid').attr('aria-invalid', 'false');
          }
          return !errorMessage;
        }

        $('input, textarea, select').each(function () {
          var field = $(this);
          if (!field.data('validation')) return;
          if (!field.siblings('.validation-error').length &&
              !$('#' + (field.attr('id') || field.attr('name')) + 'Error').length) {
            $('<span class="validation-error error-text" role="alert"></span>').insertAfter(field);
          }
        });

        $(document).on('input change blur', 'input, textarea, select', function () {
          if ($(this).data('validation')) validateField(this);
        });

        $('form').on('submit', function (e) {
          var form = this;
          var isValid = true;
          $(form).find('input, textarea, select').each(function () {
            if ($(this).data('validation') && !validateField(this)) isValid = false;
          });
          if (!isValid) {
            e.preventDefault();
            $(form).find('.is-invalid').first().trigger('focus');
          }
        });
      })(window.jQuery);
    }
  });

  /* --- Global MediMax helpers --- */
  window.MediMax = window.MediMax || {
    toast: function (msg, icon) {
      icon = icon || 'fa-info-circle';
      var container = document.querySelector('.toast-stack');
      if (!container) {
        container = document.createElement('div');
        container.className = 'toast-stack position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
      }
      var toastEl = document.createElement('div');
      toastEl.className = 'alert alert-dark d-flex align-items-center gap-2 shadow-sm rounded-3 mb-2';
      toastEl.style.cssText = 'background: #1B3E5E; color: #fff; border: 0; padding: .65rem 1rem; font-size: .88rem;';
      toastEl.innerHTML = '<i class="fas ' + icon + '"></i><span>' + msg + '</span>';
      container.appendChild(toastEl);
      setTimeout(function () {
        toastEl.remove();
      }, 3000);
    }
  };
})();
