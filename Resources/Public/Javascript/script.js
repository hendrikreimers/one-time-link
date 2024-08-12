
function load() {
    const tag = document.querySelector('script#script');
    const shortUrlInput = document.querySelector('input#shorturl');
    let statusTimeout = null;


    /**
     * Helper function to show copy to clipboard status
     *
     * @param type
     * @param msg
     */
    function setStatusDiv(type, msg) {
        const statusDiv = document.querySelector('div.status');

        if (statusTimeout) clearTimeout(statusTimeout);

        if (statusDiv) {
            statusDiv.classList.add(type);
            statusDiv.innerText = msg;
            statusTimeout = setTimeout(() => {
                statusDiv.innerText = '';
                statusDiv.classList.remove('error');
                statusDiv.classList.remove('success');
            }, 1000);
        }
    }

    // URL rewriter (without browser history)
    if (tag && tag.dataset.check) {
        window.location.replace(atob(tag.dataset.check));
    }

    // On Input focus
    if (shortUrlInput) {
        shortUrlInput.addEventListener('click', (evt) => {
            setTimeout(() => {
                evt.target.focus();

                setTimeout(() => {
                    evt.target.setSelectionRange(0, evt.target.value.length);

                    // Try to copy it into the clipboard
                    navigator.clipboard.writeText(evt.target.value)
                        .then(() => {
                            setStatusDiv('success', 'Copied to clipboard.');
                        })
                        .catch(err => {
                            setStatusDiv('error', 'Error copying to clipboard: ' + err.message);
                        });
                }, 50);
            }, 50);
        });
    }
}

// If you load this script through a different JS loader, its helpful not to wait for Load event
if (document.readyState === "loading") {
    window.addEventListener("DOMContentLoaded", () => load());
} else load();