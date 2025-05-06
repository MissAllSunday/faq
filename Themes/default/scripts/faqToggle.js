(function(currentWindow, currentDocument) {
    let faqToggleClass = 'faq_toggle',
        faqToggleLinkClass = 'faq_toggle_link',
        faqBlock = currentDocument.getElementsByClassName(faqToggleClass);

    if (!faqBlock)
        return;

    function toggleFaq(currentFaqElement) {
        currentFaqElement.style.display = 'none';
        let currentFaqAnchor = currentDocument.getElementById( currentFaqElement.id.replace('faq','link'));

        currentFaqAnchor.onclick = function(event) {
            event.preventDefault();
            let isCollapsed = currentFaqElement.style.display === 'none';

            currentFaqElement.style.display = isCollapsed ? 'block' : 'none';

            if (isCollapsed)
                currentFaqElement.classList.add('faqFadeIn');

            else
                currentFaqElement.classList.remove('faqFadeIn');
        };
    }

    for (let index = 0, len = faqBlock.length; index < len; ++index) {
        toggleFaq(faqBlock[index]);
    }
})(window, document);