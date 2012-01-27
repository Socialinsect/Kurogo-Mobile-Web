function tabletInit() {
    window.splitView.prototype.oldLinkSelect = window.splitView.prototype.linkSelect;
    window.splitView.prototype.linkSelect = function(e, link) {
        link.href = webBridgeLinkToAjaxLink(link.href);
        this.oldLinkSelect(e, link);
    };

    setOrientation(getOrientation());

    setContainerWrapperHeight();
    
    // Adjust wrapper height on orientation change or resize
    var resizeEvent = 'onorientationchange' in window ? 'orientationchange' : 'resize';
    window.addEventListener(resizeEvent, function() {setTimeout(handleWindowResize,0)}, false);
  
    document.addEventListener('touchmove', function(e) { e.preventDefault(); }, false);
    
    handleWindowResize();
  
    //run module init if present
    if (typeof moduleInit != 'undefined') {
        moduleInit();
    }
}

