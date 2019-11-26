function copyToClipboard(data) {
  let input = document.createElement('input');
  input.setAttribute('type', 'text');
  input.value = data;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
}

function toggleSettings() {
  let settings = document.getElementById('settings-wrap');
  let state = settings.style.width;
  if (state && state !== '0px') {
    settings.style.width = '0';
    window.settings_visible = false;
  } else {
    settings.style.width = '400px';
    window.settings_visible = true;
  }
}

window.addEventListener('click', (e) => {
  if (window.settings_visible) {
    toggleSettings();
  }
  console.log(e.target);
});
