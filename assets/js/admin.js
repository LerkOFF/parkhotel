const editor = document.querySelector('.rich-editor');
const htmlField = document.querySelector('.editor-html');
const form = document.querySelector('form.editor');
const uploadInput = document.querySelector('.editor-upload');
const uploadStatus = document.querySelector('.upload-status');

const syncEditor = () => {
  if (editor && htmlField) htmlField.value = editor.innerHTML.trim();
};

document.querySelectorAll('.editor-toolbar button[data-command]').forEach((button) => {
  button.addEventListener('click', () => {
    editor?.focus();
    const command = button.dataset.command;
    let value = button.dataset.value || null;
    if (command === 'createLink') {
      value = window.prompt('Введите адрес ссылки:');
      if (!value) return;
    }
    document.execCommand(command, false, value);
    syncEditor();
  });
});

uploadInput?.addEventListener('change', async () => {
  const file = uploadInput.files?.[0];
  if (!file) return;
  uploadStatus.textContent = 'Загружаем файл…';
  uploadStatus.className = 'upload-status loading';
  const data = new FormData();
  data.append('file', file);
  data.append('csrf', window.PARKHOTEL_EDITOR.csrf);
  try {
    const response = await fetch('/admin/upload', {method: 'POST', body: data, headers: {Accept: 'application/json'}});
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.message || 'Не удалось загрузить файл.');
    editor.focus();
    const safeName = document.createElement('span');
    safeName.textContent = result.name;
    const html = result.image
      ? `<figure><img src="${result.url}" alt="${safeName.innerHTML}"><figcaption>${safeName.innerHTML}</figcaption></figure><p><br></p>`
      : `<p><a href="${result.url}" target="_blank" rel="noopener noreferrer">${safeName.innerHTML}</a></p>`;
    document.execCommand('insertHTML', false, html);
    syncEditor();
    uploadStatus.textContent = 'Файл добавлен в текст.';
    uploadStatus.className = 'upload-status success';
  } catch (error) {
    uploadStatus.textContent = error.message;
    uploadStatus.className = 'upload-status error';
  } finally {
    uploadInput.value = '';
  }
});

editor?.addEventListener('input', syncEditor);
form?.addEventListener('submit', syncEditor);
