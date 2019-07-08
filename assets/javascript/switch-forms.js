/**
 * Form theme swticher
 * Used for demonstration purposes only
 */

(function () {
  document.getElementById('toggle').addEventListener('click', function (e) {
    e.preventDefault();

    if (typeof subscribeForm !== 'object') {
      return;
    }

    var form = document.getElementById('subscribe-form');

    for (var theme in subscribeForm) {
      var cl = 'subscribe-form--' + theme;

      if (form.classList.contains(cl)) {
        form.classList.remove(cl);
        continue;
      }

      for (var item in subscribeForm[theme]) {
        var selector = '.subscribe-form__' + item;

        form.querySelector(selector).innerHTML = subscribeForm[theme][item];
      }

      form.classList.add(cl);
    }
  })
})();