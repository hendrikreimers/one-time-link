window.addEventListener("load", (evt) => {
    const tag = document.querySelector('script#script');
    
    if ( tag && tag.dataset.check ) {
        window.location.replace(atob(tag.dataset.check));
    }
    
    const shortUrlInput = document.querySelector('input#shorturl');
    
    if ( shortUrlInput ) {
        shortUrlInput.addEventListener('click', (evt) => {
            evt.target.focus();
            evt.target.select();
        });
    }
});