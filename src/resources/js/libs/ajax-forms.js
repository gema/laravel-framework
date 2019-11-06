// Ajax-forms
export function init() {
    queryAll(".ajax-form").forEach(form => {
        let alert = form.query(".alert.alert-info-success");
        let loadingForm = form.query(".loadingForm");
        let sendButton = form.query(".send-button");
        let inputs = form.queryAll("input:not([type='submit']),textarea,select");

        sendButton.addEventListener("click", function(e) {
            e.preventDefault();

            sendButton.classList.add("active");

            let data = new FormData(form);
            let urlPath = form.getAttribute("action");

            inputs.forEach(elem => elem.disabled = true);

            fetch(urlPath, {
                method: "POST",
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.query("input[name='_token']").value,
                },
                body: data,
            }).
            then(response => {
                response.text().then(result => {
                    let data = JSON.parse(result);
                    let message = data.message;

                    // clear errors
                    form.queryAll(".error-message").forEach(e => e.innerHTML = '');

                    // success
                    if(!data.errors || Object.keys(data.errors).length == 0) {
                        alert.style.display = "block";
                        alert.innerHTML = `<p>${message}</p>`;
                        form.reset();
                    }

                    // error
                    for (let error in data.errors) {
                        let input = form.query(`[name="${error}"`);

                        if(input) {
                            let div = input.closest('div');
                            div.classList.add("error");
                            div.query(".error-message").innerHTML = data.errors[error];
                        }
                    };
                })
            }).catch(e => {

            }).finally(function() { 
                sendButton.classList.remove("active");

                inputs.forEach(elem => elem.disabled = false);
            });
        });
    });
}