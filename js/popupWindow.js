/**
 * a class representing a popup window (div) and all its possible elements
 * the popup is placed on the fullscreen background, which is a part of the class
 * elements:
 * - heading
 * - content panel
 * - buttons panel with yes / no buttons
 */
class PopupWindow
{
    /**
     * bool parameters determine which elements are created
     * @param {string} backgroundId
     * @param {bool} heading
     * @param {bool} closeWithEsc
     * @returns {PopupWindow}
     */
    constructor(backgroundId, heading = false, closeWithEsc = false)
    {
        this.backgroundId = backgroundId;
        
        // main popup window
        this.mainWindow = document.createElement("div");   
        this.mainWindow.classList.add("popupWindow");
        this.mainWindow.classList.add("emerge");
        
        // heading
        if (heading)
        {
            this.heading = document.createElement("div");
            this.heading.classList.add("popupHeading");
            this.mainWindow.appendChild(this.heading);
            
            // draggable
            this.heading.style.cursor = "move";
            this.x = 0;
            this.y = 0;
            this.deltaX = 0;
            this.deltaY = 0;
            this.heading.onmousedown = (e) =>
            {
                e = e || window.event;
                e.preventDefault();
                this.x = e.clientX;
                this.y = e.clientY;
                document.onmousemove = (e) =>
                {
                    e = e || window.event;
                    e.preventDefault();
                    this.deltaX = this.x - e.clientX;
                    this.deltaY = this.y - e.clientY;
                    this.x = e.clientX;
                    this.y = e.clientY;
                    this.mainWindow.style.top = (this.mainWindow.offsetTop - this.deltaY) + "px";
                    this.mainWindow.style.left = (this.mainWindow.offsetLeft - this.deltaX) + "px";
                };
                document.onmouseup = () =>
                {
                    document.onmousemove = null;
                    document.onmouseup = null;
                };
            };
        }
        else
            this.heading = null;
        
        // content panel
        this.contentPanel = document.createElement("div");
        this.contentPanel.classList.add("popupContentPanel");
        this.mainWindow.appendChild(this.contentPanel);
        
        // buttons panel
        this.buttonsPanel = document.createElement("div");
        this.mainWindow.appendChild(this.buttonsPanel);

        // yes button
        this.yesBtn = document.createElement("button");
        this.yesBtn.innerText = "Yes";
        this.yesBtn.classList.add("button", "popup-button");
        this.buttonsPanel.appendChild(this.yesBtn);

        // no button
        this.noBtn = document.createElement("button");
        this.noBtn.innerText = "No";
        this.noBtn.classList.add("button", "button-red", "popup-button");
        this.noBtn.onclick = this.cancel;
        this.buttonsPanel.appendChild(this.noBtn);
        
        // background - always present
        this.background = document.createElement("div");
        this.background.classList.add("fullscreenBackground");
        this.background.setAttribute("id", this.backgroundId);
        this.background.appendChild(this.mainWindow);
        
        // closing with escape key
        if (closeWithEsc)
        {
            this.background.onkeydown = (e) =>
            {
                if (e.key === "Escape")
                    this.cancel();
            };
            this.mainWindow.onkeydown = (e) =>
            {
                if (e.key === "Escape")
                    this.cancel();
            };
        }
    }
    
    apply()
    {
        document.body.append(this.background);
        document.activeElement.blur();
        this.background.tabIndex = 0;
        this.background.focus();
    }
    
    cancel = () =>
    {
        document.getElementById(this.backgroundId).remove();
    }
}

/**
 * displays a popup window containing a message only
 * the window can be cancelled by a clik anywhere or by a keydown (any key)
 * @param {string} message
 */
function showPopupMessage(message)
{
    document.activeElement.blur();
    let popup = new PopupWindow("popupMessageId", false, false, false);
    popup.contentPanel.innerText = message;
    popup.buttonsPanel.remove();
    popup.background.onclick = popup.cancel;
    popup.background.onkeydown = popup.cancel;
    popup.background.tabIndex = 0;
    popup.mainWindow.onclick = popup.cancel;
    popup.mainWindow.onkeydown = popup.cancel;
    popup.apply();
    popup.background.focus();
}
