document.getElementById('uploadForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alertBox');
    const formData = new FormData(this);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading & Validating...';
    alertBox.className = 'alert hidden';

    try {
        const response = await fetch('upload.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        alertBox.classList.remove('hidden');
        if (result.status === 'success') {
            alertBox.className = 'alert success';
            let msg = `<strong>Success!</strong> ${result.message}`;
            if (result.warnings && result.warnings.length > 0) {
                msg += `<br><small>Skipped files: ${result.warnings.join(', ')}</small>`;
            }
            alertBox.innerHTML = msg;
            document.getElementById('photos').value = '';
        } else {
            alertBox.className = 'alert error';
            let errText = `<strong>Error:</strong> ${result.message}`;
            if (result.details && result.details.length > 0) {
                errText += `<ul>${result.details.map(d => `<li>${d}</li>`).join('')}</ul>`;
            }
            alertBox.innerHTML = errText;
        }
    } catch (err) {
        alertBox.className = 'alert error';
        alertBox.textContent = 'Network error occurred while uploading photos.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Upload & Play on Display';
    }
});
