const languageLinks = document.querySelectorAll('.languageButton');

languageLinks.forEach((languageLink) => {
    const language = languageLink.dataset.lang;
    if (!language) return;

    const formData = new FormData();
    formData.append('locale', language);

    languageLink.addEventListener('click', () => {
        fetch('/api/locale/change', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
    });
});