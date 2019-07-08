/**
 * Send form ajax handler
 */

(function () {
  /**
   * Get form element by ID
   */
  var form = document.getElementById('subscribe-form');

  /**
   * Toggle error element with custom label
   */
  function toggleError(hide, label) {
    var error = form.querySelector('.subscribe-form__error');

    // Set default label error attribute
    if (!error.hasAttribute('data-label')) {
      error.setAttribute('data-label', error.textContent);
    }

    // Hide error
    if (hide) {
      return error.classList.remove('subscribe-form__error--show');
    }

    // Set default label
    if (typeof label === 'undefined') {
      label = error.getAttribute('data-label');
    }

    error.textContent = label;
    error.classList.add('subscribe-form__error--show');
  }

  /**
   * Disable/enable submit button on request
   */
  function ajaxLoader(start) {
    var submit = form.querySelector('.subscribe-form__submit');

    // Swap submit button label
    var toggle = submit.getAttribute('data-toggle');
    submit.setAttribute('data-toggle', submit.textContent)
    submit.textContent = toggle;

    if (start) {
      return submit.setAttribute('disabled', 'disabled');
    }

    return submit.removeAttribute('disabled', 'disabled');
  }

  /**
   * Form submit event
   */
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Hide form error
    toggleError(true);

    var fields = form.querySelectorAll('.subscribe-form__fields__input');
    var data = new FormData();

    for (var i = 0, field; field = fields[i]; i++) {
      data.append(field.name, field.value);
    }

    // Start loader
    ajaxLoader(true);

    // Create ajax request
    var request = new XMLHttpRequest();
    request.open('POST', '/skyeng-subscribe/send-form.php', true);

    // Stop ajax loader on xhr done
    request.onreadystatechange = function () {
      if (request.readyState === 4) {
        ajaxLoader(false);
      }
    };

    // Request succesed
    request.onload = function () {
      if (request.status !== 200) {
        return toggleError(false);
      }

      var response = JSON.parse(request.responseText);
      response.success = response.success || false;

      if (!response.success) {
        return toggleError(false, response.message);
      }

      var content = form.querySelector('.subscribe-form__content');
      content.classList.add('subscribe-form__content--result');
    };

    // Server maybe unavailble
    request.onerror = function () {
      return toggleError(false);
    };

    // Send request
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    request.send(data);
  });
})();