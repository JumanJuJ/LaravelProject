import './bootstrap';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken && window.axios) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('hashtag-edit-toggle');
    const formWrapper = document.getElementById('hashtag-form-wrapper');
    const hashtagForm = document.getElementById('hashtag-form');
    const statusMessage = document.getElementById('hashtag-form-status');

    if (toggleButton && formWrapper) {
        toggleButton.addEventListener('click', () => {
            formWrapper.classList.toggle('hidden');
            toggleButton.textContent = formWrapper.classList.contains('hidden')
                ? 'Modifica hashtag'
                : 'Chiudi modifica hashtag';
        });
    }

    if (hashtagForm && window.axios) {
        hashtagForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(hashtagForm);

            try {
                const response = await window.axios.post(hashtagForm.action, formData, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                statusMessage.textContent = response.data.message || 'Hashtag aggiornati con successo.';
                statusMessage.classList.remove('text-error');
                statusMessage.classList.add('text-success');

                setTimeout(() => {
                    statusMessage.textContent = '';
                }, 4000);
            } catch (error) {
                statusMessage.textContent = error.response?.data?.message || 'Errore durante l’aggiornamento degli hashtag.';
                statusMessage.classList.remove('text-success');
                statusMessage.classList.add('text-error');
            }
        });
    }
});
