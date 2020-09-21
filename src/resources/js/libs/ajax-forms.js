// Ajax-forms
// eslint-disable-next-line import/prefer-default-export
export function init() {
  queryAll('.ajax-form').forEach(form => {
    const alert = form.query('.alert.alert-info-success');
    const sendButton = form.query('.send-button');
    const inputs = form.queryAll("input:not([type='submit']),textarea,select");

    sendButton.addEventListener('click', e => {
      e.preventDefault();

      sendButton.classList.add('active');

      const data = new FormData(form);
      const urlPath = form.getAttribute('action');

      inputs.forEach(elem => elem.disabled = true);

      fetch(urlPath, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': form.query("input[name='_token']").value,
        },
        body: data,
      })
        .then(response => {
          response.text().then(result => {
            const res = JSON.parse(result);
            const { message } = res;

            // clear errors
            form.queryAll('.error-message').forEach(err => err.innerHTML = '');

            // success
            if (!res.errors || Object.keys(res.errors).length === 0) {
              alert.style.display = 'block';
              alert.innerHTML = `<p>${message}</p>`;
              form.reset();
            }

            // error
            Object.values(res.errors).forEach(error => {
              const input = form.query(`[name="${error}"`);

              if (input) {
                const div = input.closest('div');
                div.classList.add('error');
                div.query('.error-message').innerHTML = res.errors[error];
              }
            });
          });
        }).catch(() => {

        }).finally(() => {
          sendButton.classList.remove('active');

          inputs.forEach(elem => elem.disabled = false);
        });
    });
  });
}
