document.addEventListener("DOMContentLoaded", function () {
  //Toggle menu mobile 
  const menuToggle = document.querySelector(".menu-toggle");
  const mainNav = document.querySelector(".main-navigation");

  if (menuToggle && mainNav) {
    menuToggle.addEventListener("click", function () {
      if (mainNav.classList.contains("active")) {
        mainNav.classList.remove("active");
        mainNav.classList.add("closing");
        menuToggle.classList.remove("active");

        setTimeout(() => {
          mainNav.classList.remove("closing");
        }, 400); // thời gian animation
      } else {
        mainNav.classList.add("active");
        menuToggle.classList.add("active");
      }
    });
  }

  //Reveal on scroll 
  const elements = document.querySelectorAll(".reveal");
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("active");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  elements.forEach(el => observer.observe(el));
  
});

// Category bar with scroll buttons
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-catwrap]').forEach((wrap) => {
    const bar  = wrap.querySelector('[data-catbar]');
    const prev = wrap.querySelector('[data-prev]');
    const next = wrap.querySelector('[data-next]');
    if (!bar || !prev || !next) return;

    // Số mục hiển thị/khung (đọc từ CSS --visible, fallback 3)
    const getVisibleCount = () => {
      const v = getComputedStyle(bar).getPropertyValue('--visible').trim();
      const n = parseInt(v || '0', 10);
      return n > 0 ? n : 3;
    };

    // Có cần cuộn không?
    const hasScroll = () => bar.scrollWidth > bar.clientWidth + 1;

    const updateBtns = () => {
      const max = bar.scrollWidth - bar.clientWidth - 1;
      prev.classList.toggle('is-disabled', bar.scrollLeft <= 0);
      next.classList.toggle('is-disabled', bar.scrollLeft >= max);
      // Fade mép
      wrap.classList.toggle('at-start', bar.scrollLeft <= 0);
      wrap.classList.toggle('at-end',   bar.scrollLeft >= max);
    };

    const updateHasScroll = () => {
      const need = hasScroll();
      wrap.classList.toggle('has-scroll', need); // <- chỉ khi có scroll mới hiện nút (CSS)
      if (!need) {
        // Không cần cuộn: reset nút & vị trí
        bar.scrollLeft = 0;
        prev.classList.add('is-disabled');
        next.classList.add('is-disabled');
      }
      updateBtns();
    };

    const pageWidth = () => bar.clientWidth;  // một “trang” = đúng khung nhìn (3/4/5 mục)
    const snapScroll = (dir) => {
      bar.scrollBy({ left: dir * pageWidth(), behavior: 'smooth' });
      setTimeout(updateBtns, 350);
    };

    prev.addEventListener('click', () => snapScroll(-1));
    next.addEventListener('click', () => snapScroll(1));
    bar.addEventListener('scroll', updateBtns, { passive: true });
    window.addEventListener('resize', updateHasScroll);

    // Kéo ngang bằng trackpad/mouse
    bar.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;
      e.preventDefault();
      bar.scrollLeft += e.deltaX;
      updateBtns();
    }, { passive: false });

    // Khởi tạo
    updateHasScroll();
  });
});
// ========== 4. Product variants (color/image) ==========
// ===== Related slider: HƯƠNG VỊ =====
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-relviewport]').forEach(function (vp) {
    const track = vp.querySelector('[data-reltrack]');
    const prev  = vp.querySelector('[data-relprev]');
    const next  = vp.querySelector('[data-relnext]');
    if (!track || !prev || !next) return;

    // Bật smooth-scroll (nếu trình duyệt hỗ trợ)
    track.style.scrollBehavior = 'smooth';

    // Lấy gap (column-gap | gap) từ CSS
    const getGap = () => {
      const cs = getComputedStyle(track);
      const g1 = parseFloat(cs.columnGap || '0');
      const g2 = parseFloat(cs.gap || '0');
      return (Number.isFinite(g1) && g1 > 0) ? g1 : (Number.isFinite(g2) ? g2 : 0);
    };

    // Bước trượt = width 1 card + gap
    const getStep = () => {
      const card = track.querySelector('.orioni-card');
      if (!card) return 0;
      const rect = card.getBoundingClientRect();
      return Math.ceil(rect.width + getGap());
    };

    // Ẩn/hiện/disable nút theo vị trí
    const updateNav = () => {
      const isSmall = window.matchMedia('(max-width:1199.98px)').matches;
      const overflow = track.scrollWidth - track.clientWidth > 1;

      prev.style.display = (isSmall && overflow) ? '' : 'none';
      next.style.display = (isSmall && overflow) ? '' : 'none';

      const max = track.scrollWidth - track.clientWidth;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= max - 2;
    };

    // Click handlers
    prev.addEventListener('click', () => {
      const step = getStep();
      track.scrollTo({ left: Math.max(0, track.scrollLeft - step), behavior: 'smooth' });
    });
    next.addEventListener('click', () => {
      const step = getStep();
      const max  = track.scrollWidth - track.clientWidth;
      track.scrollTo({ left: Math.min(max, track.scrollLeft + step), behavior: 'smooth' });
    });

    // Cập nhật khi scroll/resize
    track.addEventListener('scroll', updateNav, { passive: true });
    window.addEventListener('resize', () => setTimeout(updateNav, 80), { passive: true });

    // Lần đầu
    updateNav();
  });
});

// ====== GALLERY BIẾN THỂ (180ml / 90ml) ======
function initWeightVariants() {
  // Tìm đúng gallery có chế độ biến thể
  document.querySelectorAll('[data-variants]').forEach(function (wrap) {
    const root = wrap.closest('.orioni-single__grid') || document;
    const imgs = Array.from(wrap.querySelectorAll('.vimg[data-variant]'));         // ảnh lớn
    const btns = Array.from(root.querySelectorAll('.js-variant[data-variant]'));   // nút 180/90
    if (!imgs.length || !btns.length) return;

    const show = (key) => {
      imgs.forEach(img => img.classList.toggle('is-show', img.dataset.variant === key));
      btns.forEach(btn => btn.classList.toggle('is-active', btn.dataset.variant === key));
    };

    btns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const key = btn.dataset.variant;
        if (key) show(key);
      });
    });

    // Hiển thị biến thể đầu tiên
    const firstKey = (btns[0] && btns[0].dataset.variant) || (imgs[0] && imgs[0].dataset.variant);
    if (firstKey) show(firstKey);
  });
}

document.addEventListener('DOMContentLoaded', function () {
  initWeightVariants();   // Gọi sau khi DOM sẵn sàng
});

// ====== Lightbox cho "Bộ sưu tập" ======
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-collection]').forEach(function (wrap) {
    const items = [...wrap.querySelectorAll('[data-zoom]')];
    const box   = wrap.querySelector('[data-ocbox]');
    if (!items.length || !box) return;

    const imgEl = box.querySelector('[data-ocimg]');
    const capEl = box.querySelector('[data-occap]');
    const btnClose = box.querySelector('[data-occlose]');
    const btnPrev  = box.querySelector('[data-ocprev]');
    const btnNext  = box.querySelector('[data-ocnext]');

    let idx = 0;

    function open(i){
      idx = i;
      const a = items[idx];
      imgEl.src = a.dataset.full || a.href;
      imgEl.alt = a.querySelector('img')?.alt || '';
      capEl.textContent = a.dataset.caption || '';
      box.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function close(){
      box.classList.remove('is-open');
      imgEl.src = '';
      document.body.style.overflow = '';
    }
    function prev(){ idx = (idx - 1 + items.length) % items.length; open(idx); }
    function next(){ idx = (idx + 1) % items.length; open(idx); }

    // Click thumbnail
    items.forEach((a, i) => {
      a.addEventListener('click', function(e){
        e.preventDefault();
        open(i);
      });
    });

    // Nav / close
    btnClose.addEventListener('click', close);
    btnPrev.addEventListener('click', function(e){ e.stopPropagation(); prev(); });
    btnNext.addEventListener('click', function(e){ e.stopPropagation(); next(); });

    // Đóng khi click nền tối
    box.addEventListener('click', function(e){
      if (e.target === box) close();
    });

    // ESC/←/→
    document.addEventListener('keydown', function(e){
      if (!box.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') prev();
      else if (e.key === 'ArrowRight') next();
    });
  });
});

// ====== Chọn biến thể (hộp 2P/6P/...) và chọn loại sản phẩm======
document.addEventListener('DOMContentLoaded', function () {
  // ===== DẠNG HỘP (2P/6P/...) =====
  document.querySelectorAll('.js-pack').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const key = btn.getAttribute('data-pack');     // ví dụ p1, p2, ...
      const w   = btn.getAttribute('data-weight') || '';

      // active chip
      const wrap = btn.closest('.o-values');
      wrap.querySelectorAll('.js-pack').forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      // đổi ảnh tương ứng
      const gallery = document.querySelector('[data-packs]');
      if (gallery) {
        gallery.querySelectorAll('.g-main').forEach(img => img.classList.remove('is-show'));
        const target = gallery.querySelector('.g-main[data-pack="'+ key +'"]');
        if (target) target.classList.add('is-show');
      }

      // cập nhật chip trọng lượng
      const wChip = document.querySelector('.js-pack-weight');
      if (wChip) wChip.textContent = w || '—';
    });
  });

  // ===== 180/90 (nếu bạn vẫn dùng) =====
  document.querySelectorAll('.js-variant').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const key = btn.getAttribute('data-variant');
      const wrap = btn.closest('.o-values');
      wrap.querySelectorAll('.js-variant').forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      const gallery = document.querySelector('[data-variants]');
      if (gallery) {
        gallery.querySelectorAll('.g-main').forEach(img => img.classList.remove('is-show'));
        const target = gallery.querySelector('.g-main[data-variant="'+ key +'"]');
        if (target) target.classList.add('is-show');
      }
    });
  });
});


document.addEventListener('DOMContentLoaded', function () {
  // ====== Collection preview limiter (+N tile) ======
  document.querySelectorAll('[data-collection-grid]').forEach(function(grid){
    const items   = Array.from(grid.querySelectorAll('.oc-it'));      // các ảnh
    const moreLi  = grid.querySelector('[data-more-tile]');            // tile +N
    const moreBtn = moreLi ? moreLi.querySelector('[data-more]') : null;
    const moreTxt = moreLi ? moreLi.querySelector('[data-moretext]') : null;
    const moreImg = moreLi ? moreLi.querySelector('.oc-more__img') : null;

    if (!items.length || !moreLi) return;

    function limitForWidth(w){
      if (w >= 1200) return 3;    // Desktop
      if (w >= 768)  return 2;    // Tablet
      return 1;                   // Mobile
    }

    function apply(){
      const w = window.innerWidth || document.documentElement.clientWidth;
      const limit  = limitForWidth(w);
      const total  = items.length;
      const hidden = Math.max(total - limit, 0);

      // Ẩn/hiện item
      items.forEach((li, idx) => { li.style.display = (idx < limit) ? '' : 'none'; });

      if (hidden > 0) {
        moreLi.style.display = '';
        moreTxt.textContent  = '+' + hidden;

        const nextThumb = items[limit].querySelector('img');
        if (nextThumb && moreImg) moreImg.src = nextThumb.currentSrc || nextThumb.src;

        // chèn tile +N đúng vị trí (ngay trước phần ảnh bị ẩn đầu tiên)
        const refNode = items[limit] || null;
        grid.insertBefore(moreLi, refNode);

        // click +N -> mở lightbox bắt đầu từ ảnh tiếp theo
        if (moreBtn) {
          moreBtn.onclick = function(e){
            e.preventDefault();
            const nextLink = items[limit].querySelector('[data-zoom]');
            if (nextLink) nextLink.click();
          };
        }
      } else {
        moreLi.style.display = 'none';
      }
    }

    apply();
    window.addEventListener('resize', () => {
      clearTimeout(apply._t);
      apply._t = setTimeout(apply, 120);
    });
  });
});


