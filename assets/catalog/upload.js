document.addEventListener('change', event => {
    const input = event.target;
    if (input.matches('[data-catalog-upload]')) {
        input.closest('.catalog-upload').querySelector('[data-catalog-filename]').textContent = input.files[0]?.name ?? '';
    }
});
