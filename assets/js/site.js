const menuButton = document.querySelector('.menu-button');
const nav = document.querySelector('#site-nav');
menuButton?.addEventListener('click', () => {
  const open = menuButton.getAttribute('aria-expanded') === 'true';
  menuButton.setAttribute('aria-expanded', String(!open));
  nav?.classList.toggle('open', !open);
});

document.querySelectorAll('.request-form').forEach((form) => {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const status = form.querySelector('.form-status');
    const button = form.querySelector('button[type="submit"]');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    status.textContent = 'Отправляем заявку...';
    status.className = 'form-status wide loading';
    button.disabled = true;
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || 'Не удалось отправить заявку.');
      status.textContent = result.message;
      status.className = 'form-status wide success';
      form.reset();
    } catch (error) {
      status.textContent = error.message;
      status.className = 'form-status wide error';
    } finally {
      button.disabled = false;
    }
  });
});

const cookieBanner = document.querySelector('.cookie-banner');
const cookieKey = 'parkhotel_cookie_notice';
let cookieSeen = false;
try { cookieSeen = localStorage.getItem(cookieKey) === '1'; } catch (error) { cookieSeen = false; }
if (cookieBanner && !cookieSeen) {
  cookieBanner.hidden = false;
  cookieBanner.querySelector('.cookie-ok')?.addEventListener('click', () => {
    cookieBanner.hidden = true;
    try { localStorage.setItem(cookieKey, '1'); } catch (error) { /* без хранилища плашка покажется снова */ }
  });
}

const zoomables = [...document.querySelectorAll('.photo-strip img, .inner-hero figure img, .rich-content img, .menu-photo img')];
if (zoomables.length && typeof HTMLDialogElement === 'function') {
  const box = document.createElement('dialog');
  box.className = 'lightbox';
  box.setAttribute('aria-label', 'Просмотр фотографии');
  box.innerHTML = '<button type="button" class="lightbox-close" aria-label="Закрыть">&times;</button>'
    + '<button type="button" class="lightbox-step lightbox-prev" aria-label="Предыдущее фото">&lsaquo;</button>'
    + '<img alt="">'
    + '<button type="button" class="lightbox-step lightbox-next" aria-label="Следующее фото">&rsaquo;</button>';
  document.body.append(box);
  const big = box.querySelector('img');
  let index = 0;
  let touchX = null;

  const show = (i) => {
    index = (i + zoomables.length) % zoomables.length;
    big.src = zoomables[index].currentSrc || zoomables[index].src;
    big.alt = zoomables[index].alt;
  };
  const open = (i) => {
    show(i);
    box.classList.toggle('single', zoomables.length < 2);
    box.showModal();
    document.documentElement.classList.add('lightbox-open');
  };

  zoomables.forEach((img, i) => {
    img.classList.add('zoomable');
    img.tabIndex = 0;
    img.setAttribute('role', 'button');
    img.setAttribute('aria-label', 'Открыть фото: ' + img.alt);
    img.addEventListener('click', () => open(i));
    img.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        open(i);
      }
    });
  });

  box.querySelector('.lightbox-close').addEventListener('click', () => box.close());
  box.querySelector('.lightbox-prev').addEventListener('click', () => show(index - 1));
  box.querySelector('.lightbox-next').addEventListener('click', () => show(index + 1));
  box.addEventListener('click', (event) => { if (event.target === box) box.close(); });
  box.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') show(index - 1);
    if (event.key === 'ArrowRight') show(index + 1);
  });
  box.addEventListener('touchstart', (event) => { touchX = event.touches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', (event) => {
    if (touchX === null || zoomables.length < 2) return;
    const dx = event.changedTouches[0].clientX - touchX;
    touchX = null;
    if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
  });
  box.addEventListener('close', () => document.documentElement.classList.remove('lightbox-open'));
}
