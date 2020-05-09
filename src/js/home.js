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

function getDropListHTML(dropListJSON) {
  let innerHTML = '';
  for (let i in dropListJSON) {
    let drop = dropListJSON[i];
    let link = `
      <span title="drop link">
        <a href="index.php?route=upload&key=${drop.dropKey}" target="_blank">${drop.dropKey}</a>
      </span>`;
    let menu = `
      <span class='link' title="copy command to upload with cURL"
          onclick="copyToClipboard(this.getAttribute('curlurl'))"
          curlurl='${drop.curlLink}'>➿
      </span>`;
    if (drop.dropStatus === 'dropped') {
      link = `
        <span title="drop link">
          <a href="index.php?route=get&key=${drop.dropKey}" target="_blank">${drop.fileName}</a>
        </span>`;
      menu = `
        <span class='link' title="copy command to download with wget"
            onclick="copyToClipboard(this.getAttribute('wgeturl'))"
            wgeturl='${drop.wgetLink}'>🌐
        </span>`;
    }
    innerHTML += `
      <tr class="drop-list-row">
          <td>
              <span title="${drop.dropStatus}">${drop.dropStatus === 'dropped' ? '✔' : '⏱'}</span>
          </td>
          <td>
              <span>${link}</span>
          </td>
          <td>
              <span>${drop.createDate}</span>
          </td>
          <td>
              <span>${drop.dropDate}</span>
          </td>
          <td>
              <input type="text" value="${drop.sha256hash}" title="sha256 hash: ${drop.sha256hash}" readonly="">
          </td>
          <td>
              <span>${drop.sourceIp}</span>
          </td>
          <td>
              <span class="menu-wrap">
                ${menu}
                <span title='delete'>
                    <a href='index.php?route=delete&key=${drop.dropKey}&csrf_token=${window.csrf_token}'>❌</a>
                </span>
              </span>
          </td>
      </tr>`;
  }
  if (!innerHTML) {
    innerHTML = `
      <tr>
          <td colspan="7">
              <span>you have no drops</span>
          </td>
      </tr>`;
  }

  return `
    <table class='drop-list'>
        <tr class='drop-list-heading'>
            <td>
                <span>status</span>
            </td>
            <td>
                <span>drop link</span>
            </td>
            <td>
                <span>created</span>
            </td>
            <td>
                <span>dropped</span>
            </td>
            <td>
                <span>sha256 hash</span>
            </td>
            <td>
                <span>source ip</span>
            </td>
            <td>
                <span>actions</span>
            </td>
        </tr>
        ${innerHTML}
    </table>`;
}

window.addEventListener('click', (e) => {
  if (window.settings_visible) {
    toggleSettings();
  }
});

window.addEventListener('load', (e) => {
  document.getElementById('list-wrap').innerHTML = getDropListHTML(dropListJSON);
});
