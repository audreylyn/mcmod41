document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('msg');

    if (status && message) {
        const title = status === 'success' ? 'Success' : 'Error';
        showModal(title, decodeURIComponent(message), status);
    }
});