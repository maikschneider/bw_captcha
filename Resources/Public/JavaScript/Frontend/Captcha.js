window.BwCaptcha = function () {
  document.querySelectorAll('a.captcha__reload:not([data-initialized="true"])').forEach(function (element) {
    element.addEventListener('click', function (event) {
      event.preventDefault();
      const div = event.currentTarget.parentElement;
      div.classList.add('captcha--reloading', 'captcha--spin');
      let captchaUrl = event.currentTarget.dataset.url;
      event.currentTarget.previousElementSibling.setAttribute('src', captchaUrl + (/\?/.test(captchaUrl) ? '&' : '?') + 'now=' + Date.now());
      setTimeout(function () {
        div.classList.remove('captcha--spin')
      }, 400);
    });
    element.setAttribute('data-initialized', 'true');
  })

  document.querySelectorAll('.captcha img[aria-live="polite"]:not([data-initialized="true"])').forEach(function (element) {
    element.addEventListener('load', function (event) {
      const div = event.currentTarget.parentElement;
      div.classList.remove('captcha--reloading');
    })
    element.setAttribute('data-initialized', 'true');
  })

  document.querySelectorAll('a.captcha__audio:not([data-initialized="true"])').forEach(function (element) {
    element.addEventListener('click', function (event) {
      event.preventDefault();
      const div = event.currentTarget.parentElement;
      window.captchaAudio = window.captchaAudio ? window.captchaAudio : new Audio();
      window.captchaAudio.addEventListener('ended', () => div.classList.remove('captcha--playing'));
      if (window.captchaAudio.paused) {
        div.classList.add('captcha--playing');
        const img = div.querySelector('img');
        const c = document.createElement('canvas');
        const ctx = c.getContext('2d');
        const data = new FormData();
        c.width = img.naturalWidth;
        c.height = img.naturalHeight;
        ctx.drawImage(img, 0, 0);
        data.append('captchaDataUrl', c.toDataURL());
        const audioUrl = event.currentTarget.dataset.url;
        fetch(audioUrl, {method: 'POST', body: data})
          .then(response => response.blob())
          .then(blob => {
            window.captchaAudio.src = window.URL.createObjectURL(blob);
            window.captchaAudio.play();
          });
      } else {
        window.captchaAudio.pause();
        div.classList.remove('captcha--playing')
      }
    })
    element.setAttribute('data-initialized', 'true');
  });
}

window.BwCaptcha();
