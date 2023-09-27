const oscAmazonPayTagetNodeSelectorTwig = 'content';
const oscAmazonPayTagetNodeSelectorApex = 'details_container';
const oscAmazonPayObserverConfig = { attributes: true, childList: true, subtree: true };

// Twig theme
oscAmazonPayTargetNode = document.getElementById(oscAmazonPayTagetNodeSelectorTwig);
// Apex Theme
if (typeof oscAmazonPayTargetNode === "undefined" || oscAmazonPayTargetNode === null ) {
    oscAmazonPayTargetNode = document.getElementById(oscAmazonPayTagetNodeSelectorApex);
}

// callback function triggers when the observer detects changes in the variant dropdown
const callback = (mutationList, observer) => {
    for (const mutation of mutationList) {
        if (mutation.type === "childList") {
            // after the dropdown is changed, force re-rendering of the amazonpay button
            oscAmazonPayButtonIsRendered = false;
            oscAmazonPayRenderAmazonButton();
            // check the state of the oxid basket and set the amazonpay button state accordingly
            oscAmazonPaySetAmazonButtonState();
            // re-register the amazonpay button onClick handler
            // this gets removed every time when oxid replaces the html source code for the dropdowns
            oscAmazonPayRegisterAmazonPayClickHandler();
        }

    }
};

// define and start the mutation observer
// note that the observed element must be outside of the replaced html
// otherwise the observer will stop working because javascript events will be removed after oxid replaces the html source
const oscAmazonPayObserver = new MutationObserver(callback);
oscAmazonPayObserver.observe(oscAmazonPayTargetNode, oscAmazonPayObserverConfig);