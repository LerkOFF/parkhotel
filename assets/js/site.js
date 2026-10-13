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
