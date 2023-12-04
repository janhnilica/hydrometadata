/***********/
/* globals */
/***********/
let map = null; // main map variable
let localityMarkers = null; // list of objects { localityId, localityName, institutionId, institutionName, marker }
let newLocalityMarker; // for creating a new locality
let myIconOptions = 
{
    shadowUrl: "images/marker_icon_shadow.png",
    iconSize: [31, 40],
    shadowSize: [41, 41],
    iconAnchor: [15, 39],
    shadowAnchor: [12, 39],
    popupAnchor: [0, -36]
};
let myIconClass = L.Icon.extend({ options: myIconOptions });
let myIconBlue = new myIconClass({ iconUrl: "images/marker_icon_blue.png" });
let myIconGrey = new myIconClass({ iconUrl: "images/marker_icon_grey.png" });

/**
 * loads map into a given container
 * @param {string} containerId
 * @param {int} zoomLevel
 * @param {array} coordinates (of the center)
 * @param {function} onclick
 * @param {function} onload
 */
function loadMap(
        containerId,
        zoomLevel,
        coordinates,
        onclick = null,
        onload = null)
{
    map = L.map(containerId);
    if (onclick !== null)
        map.on("click", onclick);
    if (onload !== null)
        map.on("load", onload);
    map.setView(coordinates, zoomLevel);
    let urlTemplate = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    let options = 
    {
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright" target=_blank>OpenStreetMap</a>',
        minZoom: 3,
        maxZoom: 18
    };
    let tile = L.tileLayer(urlTemplate, options);
    tile.addTo(map);
}

/**
 * updates the map if its container's size changed
 */
function reloadMap()
{
    map.invalidateSize(false);
}

/**
 * creates a new marker with given popup content
 * @param {double} lat
 * @param {double} lng
 * @param {object} icon
 * @param {string} localityName
 * @param {int} localityId
 * @param {string} institutionName
 * @returns {marker}
 */
function createMarker(lat, lng, icon, localityName, localityId, institutionName)
{
    let marker = L.marker([lat, lng], {icon: icon});
    let popupContent = '<div class="markerpopup-heading">' + localityName + '</div>';
    popupContent += "<div class='markerpopup-line markerpopup-info'>" + institutionName + "</div>";
    popupContent += "<div class='markerpopup-line'><a class='markerpopup-ref' href='locality/" + localityId + "'>locality detail</a></div>";
    marker.bindPopup(popupContent);
    return marker;
}

/**
 * main function for creating of layers of markers
 * @param {array} institutionsList - dtb data
 * @param {array} markersData - dtb data
 */
function createLayers(institutionsList, markersData)
{
    if (institutionsList === null || markersData === [])
        return;
    localityMarkers = [];
    let layerControl = L.control.layers(null);
    let nLayers = 0;
    for (let i = 0; i < institutionsList.length; i++)
    {
        let markers = [];
        if (markersData[i] !== null)
        {
            for (let j = 0; j < markersData[i].length; j++)
            {
                let marker = createMarker(markersData[i][j].zem_sirka, markersData[i][j].zem_delka, myIconBlue, markersData[i][j].nazev, markersData[i][j].id_lokalita, institutionsList[i].nazev);
                markers.push(marker);
                localityMarkers.push({ localityId: parseInt(markersData[i][j].id_lokalita),
                    localityName: markersData[i][j].nazev,
                    institutionId: parseInt(institutionsList[i].id_instituce),
                    institutionName: institutionsList[i].nazev,
                    marker: marker });
            }
        }
        if (markers.length > 0)
        {
            nLayers += 1;
            let lg = L.layerGroup(markers);
            layerControl.addOverlay(lg, "<span style='color: var(--color_blue); margin: 10px;'>" + institutionsList[i].nazev + "</span>");
            map.addLayer(lg);
        }
    }
    if (nLayers > 0)
    {
        layerControl.addTo(map);
        //layerControl.expand();
    }
}

/**
 * opens a popup of a newly created locality
 * @param {int} newLocalityId
 */
function highlightNewLocality(newLocalityId)
{
    if (newLocalityId === null)
        return;
    for (let i = 0; i < localityMarkers.length; i++)
    {
        if (newLocalityId === localityMarkers[i].localityId)
        {
            localityMarkers[i].marker.openPopup();
            map.panTo(localityMarkers[i].marker.getLatLng());
            break;
        }
    }
}

/**
 * places the marker to the map
 * @param {event} e
 */
function placeNewLocalityMarker(e)
{
    if (newLocalityMarker === null)
    {
        newLocalityMarker = L.marker([e.latlng.lat, e.latlng.lng], {icon: myIconBlue});
        newLocalityMarker.addTo(map);
    }
    else
        newLocalityMarker.setLatLng([e.latlng.lat, e.latlng.lng]);
}

/**
 * saves the coordinates of map click and displays the newLocalityMarker
 * @param {event} e
 */
function clickToLocate(e)
{
    placeNewLocalityMarker(e);
    document.getElementById("zem_sirka").value = e.latlng.lat;
    document.getElementById("zem_delka").value = e.latlng.lng;
}

/**
 * adds the new locality marker into the map
 */
function showNewLocalityMarker()
{
    if (newLocalityMarker !== null)
        newLocalityMarker.addTo(map);
}

/**
 * creates and displays the dialogue
 */
function selectLocationDialogue()
{
    let popup = new PopupWindow("locationDialogueBackground", true, true);
    popup.heading.innerHTML = "Click to map and confirm by Ok button";
    
    popup.contentPanel.setAttribute("id", "contentPanelId");
    popup.contentPanel.setAttribute("style", "cursor: crosshair");
    popup.contentPanel.style.width = "800px";
    popup.contentPanel.style.height = "550px";
    
    popup.noBtn.style.display = "none";
    popup.yesBtn.innerText = "Ok";
    popup.yesBtn.onclick = popup.cancel;
    
    newLocalityMarker = null;
    popup.apply();
    
    setTimeout( function(){
        loadMap("contentPanelId", 7, [49.9, 15.3], clickToLocate, showNewLocalityMarker); 
    }, 300);
}


/**
 * displays a popup window containing a map and a locality marker
 * @param {string} name
 * @param {int} latitude
 * @param {int} longitude
 */
function showLocalityLocation(name, latitude, longitude)
{
    let popup = new PopupWindow("showLocationWindowBackground", true, true);
    popup.heading.innerText = name;
    
    popup.contentPanel.setAttribute("id", "contentPanelId");
    popup.contentPanel.style.width = "800px";
    popup.contentPanel.style.height = "550px";
    
    popup.noBtn.style.display = "none";
    popup.yesBtn.innerText = "Ok";
    popup.yesBtn.onclick = popup.cancel;
    
    popup.apply();
    setTimeout( function()
    {
        loadMap("contentPanelId", 9, [latitude, longitude]); 
        L.marker([parseFloat(latitude), parseFloat(longitude)], {icon: myIconBlue}).addTo(map);
    }, 300);
}

/**
 * opens popup of i-th marker
 * and pans the map to the marker
 * @param {integer} i
 */
function highlightMarker(i)
{
    if (localityMarkers !== null && localityMarkers.length > i)
    {
        localityMarkers[i].marker.openPopup();
        map.panTo(localityMarkers[i].marker.getLatLng());
    }
}

/**
 * displays the results in the left panel
 * changes the markers of not-searched localities
 * @param {array} results - array of locality ids
 */
function showSearchResults(results)
{
    if (results.length > 0)
    {
        let wrap = document.createElement("div");
        wrap.setAttribute("id", "searchResultsWrap");
        wrap.classList.add("search-results-wrap");
        
        let ul = document.createElement("ul");
        ul.classList.add("search-results-ul");
        for (let i = 0; i < localityMarkers.length; i++)
        {
            if (results.includes(localityMarkers[i].localityId))
                $(ul).append("<li><span class='search-results-item' onclick='highlightMarker(" + i + ");'>" + localityMarkers[i].localityName +  "</span></li>");
            else
            {
                localityMarkers[i].marker.setOpacity(0.6);
                localityMarkers[i].marker.setIcon(myIconGrey);
            }
        }
        wrap.appendChild(ul);
        
        let panel = document.getElementById("map-info-panel");
        panel.appendChild(wrap);
        panel.style.display = "initial";
        
        document.getElementById("show-search-results-button").style.display = "flex";
    }
}

/**
 * deletes the results list in the left panel
 * turns the markers back to the default settings
 * hides the left panel and show-results button
 */
function clearSearchResults()
{
    let wrap = document.getElementById("searchResultsWrap");
    if (wrap !== null)
        wrap.remove();
    if (localityMarkers !== null)
    {
        for (let i = 0; i < localityMarkers.length; i++)
        {
            localityMarkers[i].marker.setOpacity(1.0);
            localityMarkers[i].marker.setIcon(myIconBlue);
        }
    }
    document.getElementById("map-info-panel").style.display = "none";
    document.getElementById("show-search-results-button").style.display = "none";
}

/**
 * redirects to the save results page
 * @param {array} results - array of localities ids
 */
function saveSearchResults(results)
{
    window.location = "/download/" + results.join("/");
}

/**
 * shows / hides the search results panel
 * @param {type} element
 */
function toggleSearchResults(element)
{
    event.stopPropagation();
    let state = element.getAttribute("state");
    let panel = document.getElementById("map-info-panel");
    if (state === "visible")
    {
        panel.style.display = "none";
        element.setAttribute("state", "hidden");
        element.firstElementChild.src = "images/right.png";
        element.setAttribute("title", "Show results");
        element.style.left = "10px";
    }
    else
    {
        panel.style.display = "initial";
        element.setAttribute("state", "visible");
        element.firstElementChild.src = "images/left.png";
        element.setAttribute("title", "Hide results");
        element.style.left = "220px";
    }
}
