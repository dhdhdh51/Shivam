/**
 * LuxeEstate - Main JavaScript
 * Features: Header scroll, AJAX forms, Popup, Filters, Gallery, Animations
 */

(function () {
  'use strict';

  // ===================== HEADER SCROLL =====================
  const header = document.getElementById('site-header');
  if (header) {
    const handleScroll = () => {
      header.classList.toggle('scrolled', window.scrollY > 80);
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // ===================== HAMBURGER MENU =====================
  const hamburger = document.querySelector('.hamburger');
  const mainNav = document.querySelector('.main-nav');
  if (hamburger && mainNav) {
    hamburger.addEventListener('click', () => {
      mainNav.classList.toggle('open');
      hamburger.classList.toggle('active');
    });
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !mainNav.contains(e.target)) {
        mainNav.classList.remove('open');
        hamburger.classList.remove('active');
      }
    });
  }

  // ===================== HERO PARTICLES =====================
  function initParticles() {
    const container = document.querySelector('.hero-particles');
    if (!container) return;
    for (let i = 0; i < 18; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      const size = Math.random() * 8 + 3;
      const left = Math.random() * 100;
      const duration = Math.random() * 15 + 10;
      const delay = Math.random() * 10;
      p.style.cssText = `
        width:${size}px;height:${size}px;left:${left}%;
        animation-duration:${duration}s;animation-delay:-${delay}s;
      `;
      container.appendChild(p);
    }
  }
  initParticles();

  // ===================== COUNTER ANIMATION =====================
  function animateCounter(el) {
    const target = parseInt(el.dataset.target || el.textContent.replace(/\D/g, ''));
    const suffix = el.dataset.suffix || '';
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    const update = () => {
      current = Math.min(current + step, target);
      el.textContent = Math.floor(current).toLocaleString() + suffix;
      if (current < target) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  }

  // ===================== INTERSECTION OBSERVER =====================
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        // Counter animation
        if (entry.target.classList.contains('stat-number') || entry.target.dataset.target) {
          animateCounter(entry.target);
        }
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.animate-fade-up, .fade-up, .fade-left, .fade-right, .stat-number').forEach(el => observer.observe(el));

  // ===================== POPUP =====================
  const popup = document.getElementById('lead-popup');
  const popupClose = document.querySelectorAll('.popup-close, .popup-overlay-close');
  let popupShown = sessionStorage.getItem('popupShown');

  function showPopup() {
    if (popup && !popupShown) {
      popup.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  if (popup) {
    const delay = parseInt(popup.dataset.delay || 5000);
    setTimeout(showPopup, delay);

    // Scroll trigger
    window.addEventListener('scroll', () => {
      if (window.scrollY > window.innerHeight * 0.6 && !popupShown) {
        showPopup();
      }
    }, { passive: true, once: true });

    popupClose.forEach(btn => {
      btn.addEventListener('click', () => {
        popup.classList.remove('active');
        document.body.style.overflow = '';
        sessionStorage.setItem('popupShown', '1');
        popupShown = true;
      });
    });

    popup.addEventListener('click', (e) => {
      if (e.target === popup) {
        popup.classList.remove('active');
        document.body.style.overflow = '';
        sessionStorage.setItem('popupShown', '1');
        popupShown = true;
      }
    });
  }

  // ===================== AJAX LEAD FORM =====================
  function initLeadForms() {
    document.querySelectorAll('[data-lead-form]').forEach(form => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('[type="submit"]');
        const originalText = btn.innerHTML;
        const successDiv = form.querySelector('.form-success');
        const formBody = form.querySelector('.form-body');

        btn.innerHTML = '<svg class="spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Sending...';
        btn.disabled = true;

        try {
          const formData = new FormData(form);
          formData.append('action', 'submit_lead');
          const res = await fetch(form.action || SITE_CONFIG.ajaxUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
          });
          const data = await res.json();

          if (data.success) {
            if (successDiv && formBody) {
              formBody.style.display = 'none';
              successDiv.style.display = 'block';
            } else {
              showToast(data.message, 'success');
              form.reset();
            }
            // Close popup if inside one
            const parentPopup = form.closest('.popup-overlay');
            if (parentPopup) {
              setTimeout(() => {
                parentPopup.classList.remove('active');
                document.body.style.overflow = '';
                sessionStorage.setItem('popupShown', '1');
              }, 2000);
            }
          } else {
            showToast(data.message || 'Something went wrong. Please try again.', 'error');
          }
        } catch (err) {
          showToast('Network error. Please try again.', 'error');
        } finally {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      });
    });
  }
  initLeadForms();

  // ===================== TOAST NOTIFICATIONS =====================
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <span>${type === 'success' ? '✓' : '✕'}</span>
      <p>${message}</p>
    `;
    toast.style.cssText = `
      position:fixed;top:20px;right:20px;z-index:9999;
      background:${type === 'success' ? '#16a34a' : '#dc2626'};color:white;
      padding:14px 20px;border-radius:8px;display:flex;align-items:center;gap:10px;
      box-shadow:0 4px 20px rgba(0,0,0,0.2);font-size:0.9rem;font-weight:500;
      animation:fadeSlideUp 0.3s ease;max-width:320px;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }

  // ===================== PROPERTY FILTERS =====================
  const filterForm = document.getElementById('property-filter-form');
  const propertiesGrid = document.getElementById('properties-ajax-grid');
  const propertiesCount = document.getElementById('properties-count');
  const paginationContainer = document.getElementById('pagination');

  if (filterForm && propertiesGrid) {
    let currentPage = 1;
    let isLoading = false;

    async function loadProperties(page = 1) {
      if (isLoading) return;
      isLoading = true;
      currentPage = page;

      const formData = new FormData(filterForm);
      formData.append('page', page);
      formData.append('action', 'filter_properties');

      propertiesGrid.style.opacity = '0.5';

      try {
        const res = await fetch(SITE_CONFIG.ajaxUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          propertiesGrid.innerHTML = data.html;
          if (propertiesCount) propertiesCount.textContent = data.total;
          if (paginationContainer) paginationContainer.innerHTML = data.pagination;
          initPagination();
          // Re-observe new elements
          propertiesGrid.querySelectorAll('.animate-fade-up').forEach(el => observer.observe(el));
          window.scrollTo({ top: document.getElementById('properties-section')?.offsetTop - 100 || 0, behavior: 'smooth' });
        }
      } catch (err) {
        console.error('Filter error:', err);
      } finally {
        propertiesGrid.style.opacity = '1';
        isLoading = false;
      }
    }

    filterForm.querySelectorAll('select, input').forEach(el => {
      el.addEventListener('change', () => loadProperties(1));
    });
    filterForm.addEventListener('submit', (e) => { e.preventDefault(); loadProperties(1); });

    function initPagination() {
      document.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
          const page = parseInt(btn.dataset.page);
          if (!isNaN(page)) loadProperties(page);
        });
      });
    }
    initPagination();
  }

  // ===================== PROPERTY GALLERY =====================
  function initGallery() {
    const mainImg = document.getElementById('gallery-main-img');
    const thumbs = document.querySelectorAll('.gallery-thumb');
    if (!mainImg || !thumbs.length) return;

    thumbs.forEach(thumb => {
      thumb.addEventListener('click', () => {
        mainImg.src = thumb.dataset.full || thumb.querySelector('img').src;
        mainImg.classList.add('gallery-transition');
        setTimeout(() => mainImg.classList.remove('gallery-transition'), 300);
        thumbs.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
      });
    });

    // Keyboard navigation
    let currentIndex = 0;
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') { currentIndex = (currentIndex + 1) % thumbs.length; thumbs[currentIndex].click(); }
      if (e.key === 'ArrowLeft') { currentIndex = (currentIndex - 1 + thumbs.length) % thumbs.length; thumbs[currentIndex].click(); }
    });
  }
  initGallery();

  // ===================== SEARCH TAB =====================
  document.querySelectorAll('.search-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
    });
  });

  // ===================== HERO SEARCH FORM =====================
  const heroSearchForm = document.getElementById('hero-search-form');
  if (heroSearchForm) {
    heroSearchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const params = new URLSearchParams(new FormData(heroSearchForm));
      window.location.href = (SITE_CONFIG?.listingUrl || '/properties.php') + '?' + params.toString();
    });
  }

  // ===================== BACK TO TOP =====================
  const backToTop = document.querySelector('.back-to-top');
  if (backToTop) {
    window.addEventListener('scroll', () => {
      backToTop.classList.toggle('visible', window.scrollY > 500);
    }, { passive: true });
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ===================== LAZY LOADING =====================
  const lazyImages = document.querySelectorAll('img[data-src]');
  if ('IntersectionObserver' in window) {
    const imgObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          img.classList.remove('skeleton');
          imgObserver.unobserve(img);
        }
      });
    }, { rootMargin: '200px 0px' });
    lazyImages.forEach(img => imgObserver.observe(img));
  } else {
    lazyImages.forEach(img => { img.src = img.dataset.src; });
  }

  // ===================== WHATSAPP LINK =====================
  document.querySelectorAll('[data-whatsapp-property]').forEach(btn => {
    btn.addEventListener('click', () => {
      const propertyName = btn.dataset.whatsappProperty;
      const phone = btn.dataset.phone || (window.SITE_CONFIG?.whatsapp);
      const msg = encodeURIComponent(`Hi, I'm interested in ${propertyName}. Please share more details.`);
      window.open(`https://wa.me/${phone}?text=${msg}`, '_blank');
    });
  });

  // ===================== ADMIN: SIDEBAR TOGGLE =====================
  const adminToggle = document.getElementById('admin-sidebar-toggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  if (adminToggle && adminSidebar) {
    adminToggle.addEventListener('click', () => adminSidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (!adminSidebar.contains(e.target) && !adminToggle.contains(e.target)) {
        adminSidebar.classList.remove('open');
      }
    });
  }

  // ===================== ADMIN: IMAGE UPLOAD PREVIEW =====================
  function initImagePreview() {
    document.querySelectorAll('[data-image-upload]').forEach(input => {
      const preview = document.getElementById(input.dataset.previewTarget);
      if (!preview) return;
      input.addEventListener('change', () => {
        preview.innerHTML = '';
        [...input.files].forEach(file => {
          if (!file.type.startsWith('image/')) return;
          const reader = new FileReader();
          reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'image-preview';
            div.innerHTML = `<img src="${e.target.result}" alt="Preview"><button type="button" class="remove" onclick="this.parentNode.remove()">✕</button>`;
            preview.appendChild(div);
          };
          reader.readAsDataURL(file);
        });
      });
    });
  }
  initImagePreview();

  // ===================== ADMIN: CONFIRM DELETE =====================
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  // ===================== ADMIN: TOGGLE STATUS =====================
  document.querySelectorAll('[data-toggle-status]').forEach(el => {
    el.addEventListener('change', async () => {
      const formData = new FormData();
      formData.append('action', 'toggle_status');
      formData.append('id', el.dataset.id);
      formData.append('field', el.dataset.field);
      formData.append('value', el.checked ? 1 : 0);
      formData.append('table', el.dataset.table);

      try {
        const res = await fetch(SITE_CONFIG.adminAjaxUrl, { method: 'POST', body: formData });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
      } catch (err) {
        showToast('Update failed', 'error');
      }
    });
  });

  // ===================== ADMIN: LEAD STATUS UPDATE =====================
  document.querySelectorAll('[data-lead-update]').forEach(select => {
    select.addEventListener('change', async () => {
      const formData = new FormData();
      formData.append('action', 'update_lead');
      formData.append('id', select.dataset.leadId);
      formData.append('field', select.name);
      formData.append('value', select.value);

      try {
        const res = await fetch(SITE_CONFIG.adminAjaxUrl, { method: 'POST', body: formData });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
      } catch (err) {
        showToast('Update failed', 'error');
      }
    });
  });

  // ===================== AUTO-GENERATE SLUG =====================
  const titleInput = document.getElementById('prop-title') || document.getElementById('blog-title');
  const slugInput = document.getElementById('prop-slug') || document.getElementById('blog-slug');
  if (titleInput && slugInput && !slugInput.dataset.manual) {
    titleInput.addEventListener('input', () => {
      if (!slugInput.dataset.manual) {
        slugInput.value = titleInput.value.toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '').replace(/[\s]+/g, '-').replace(/-+/g, '-').trim('-');
      }
    });
    slugInput.addEventListener('input', () => { slugInput.dataset.manual = '1'; });
  }

  // ===================== TESTIMONIAL SLIDER (Mobile) =====================
  function initTestimonialSlider() {
    const track = document.querySelector('.testimonials-track-slider');
    if (!track || window.innerWidth > 768) return;
    let isDragging = false, startX = 0, scrollLeft = 0;

    track.addEventListener('mousedown', e => {
      isDragging = true; startX = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
    });
    track.addEventListener('mousemove', e => {
      if (!isDragging) return;
      const x = e.pageX - track.offsetLeft;
      track.scrollLeft = scrollLeft - (x - startX) * 1.5;
    });
    ['mouseup', 'mouseleave'].forEach(ev => track.addEventListener(ev, () => isDragging = false));
  }
  initTestimonialSlider();

  // Spinner CSS
  const style = document.createElement('style');
  style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}.spin{animation:spin .8s linear infinite}';
  document.head.appendChild(style);

})();
