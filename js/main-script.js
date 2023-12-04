/**
 * shows / hides the menu
 */
function toggleMenu()
{
    if ($("#menu-items").attr("is-visible") === "no")
    {
        $("#menu-items").css("display", "flex");
        $("#menu-items").attr("is-visible", "yes");
    }
    else
    {
        $("#menu-items").css("display", "none");
        $("#menu-items").attr("is-visible", "no");
    }
}

/**
 * currently opened dropdown
 * @type element
 */
let currentDropdown = null;

/**
 * shows / hides the dropdown menu
 * @param {string} dropdownId
 */
function toggleDropdown(dropdownId)
{
    if (currentDropdown !== null && currentDropdown.getAttribute("id") !== dropdownId)
        currentDropdown.style.display = "none";
    
    let dropdown = document.getElementById(dropdownId);
    if (window.getComputedStyle(dropdown).display === "none")
    {
        dropdown.style.display = "flex";
        currentDropdown = dropdown;
    }
    else
    {
        dropdown.style.display = "none";
        currentDropdown = null;
    }
}

/**
 * hides the dropdown menu when clicked elsewhere
 * @param {event} event
 */
window.onclick = function(event)
{
    if (!event.target.matches(".dropdown-launcher"))
    {
        let menu = document.getElementsByClassName("dropdown-menu");
        let i;
        for (i = 0; i < menu.length; i++)
        {
            menu[i].style.display = "none";
        }
    }
};

/**
 * toggles the visibility of variables of one locality
 * @param {element} image
 * @param {string} elementId
 */
function toggleVisibilityOneLocality(image, elementId)
{
    document.getElementById(elementId).classList.toggle("invisible");
    
    if (image.getAttribute("src") === "images/expand.png")
        image.setAttribute("src", "images/collapse.png");
    else
        image.setAttribute("src", "images/expand.png");
}

/**
 * hides / shows the variables of all localities
 * @param {element} element
 */
function toggleVisibilityAll(element)
{
    let hiders = document.querySelectorAll("[hider]");
    for (let i = 0; i < hiders.length; i++)
    {
        if (element.innerText === "Show all variables")
        {
            if (hiders[i].getAttribute("src") === "images/expand.png")
                hiders[i].click();
        }
        else
        {
            if (hiders[i].getAttribute("src") === "images/collapse.png")
                hiders[i].click();
        }
    }
    if (element.innerText === "Show all variables")
        element.innerText = "Hide all variables";
    else
        element.innerText = "Show all variables";
}

/**
 * filters the overview table according to institution
 * @param {element} element
 */
function filterInstitution(element)
{
    let value = element.value;
    let rows = document.getElementById("overviewTable").rows;
    
    let i;
    for (i = 1; i < rows.length; i++)
    {
        if (value === "all")
            rows[i].style.display = "table-row";
        else if (rows[i].getAttribute("inst") === value)
            rows[i].style.display = "table-row";
        else
            rows[i].style.display = "none";
    }
}

/**
 * displays yes/no dialogue and redirects to deletion page
 * @param {int} institutionId
 */
function verifyInstitutionDeletion(institutionId)
{
    let popup = new PopupWindow("yesNoDialogueId");
    popup.contentPanel.innerText = "Do you really want to delete the instituion with all its data and users?";
    popup.yesBtn.onclick = function(){ window.location = "institution/" + institutionId + "/delete"; };
    popup.apply();
}

/**
 * displays yes/no dialogue and redirects to deletion page
 * @param {int} userId
 */
function verifyActivitiesDeletion(userId)
{
    let popup = new PopupWindow("yesNoDialogueId");
    popup.contentPanel.innerText = "Do you really want to delete the activities?";
    popup.yesBtn.onclick = function(){ window.location = "user/" + userId + "/activities/delete"; };
    popup.apply();
}

/**
 * displays yes/no dialogue and redirects to deletion page
 * @param {int} userId
 */
function verifyUserDeletion(userId)
{
    let popup = new PopupWindow("yesNoDialogueId");
    popup.contentPanel.innerText = "Do you really want to delete the user?";
    popup.yesBtn.onclick = function(){ window.location = "user/" + userId + "/delete"; };
    popup.apply();
}

/**
 * displays yes/no dialogue and redirects to deletion page
 * @param {int} localityId
 */
function verifyLocalityDeletion(localityId)
{
    let popup = new PopupWindow("yesNoDialogueId");
    popup.contentPanel.innerText = "Do you really want to delete the locality?";
    popup.yesBtn.onclick = function(){ window.location = "locality/" + localityId + "/delete"; };
    popup.apply();
}

/**
 * displays yes/no dialogue and redirects to deletion page
 * @param {int} localityId
 * @param {int} monitoringId
 */
function verifyMonitoringDeletion(localityId, monitoringId)
{
    let popup = new PopupWindow("yesNoDialogueId");
    popup.contentPanel.innerText = "Do you really want to delete the monitored variable?";
    popup.yesBtn.onclick = function(){ window.location = "monitoring/" + localityId + "/" + monitoringId + "/delete"; };
    popup.apply();
}

/*******************/
/* locality images */
/*******************/
/**
 * clicks the hidden file input
 */
function clickImagesInput()
{
    document.getElementById("images").click();
}

/**
 * clicks the submit button
 */
function clickSubmitImages()
{
    document.getElementById("submit-images").click();
}
