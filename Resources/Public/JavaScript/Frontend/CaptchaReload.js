document.querySelectorAll('a.captcha__reload').forEach(function (element) {
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
})

document.querySelectorAll('.captcha img[aria-live="polite"]').forEach(function (element) {
  element.addEventListener('load', function (event) {
    const div = event.currentTarget.parentElement;
    div.classList.remove('captcha--reloading');
  })
})
