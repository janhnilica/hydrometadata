<?php
class MapController extends Controller
{
    public function process(array $params): void
    {
        $instMng = new InstitutionManager();
        $mapMng = new MapManager();
        
        if (count($params) > 0)
            $this->redirect(ERR_NOTFOUND);
        
        try
        {
            $institutions = $instMng->getInstitutions();
            $markersData = $mapMng->getMarkersData($institutions);
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        $this->header["title"] = $this->createTitle("Map");
        $this->header["description"] = "interactive map with locations of measuring sites";
        $this->header["keywords"] = "map, metadata, monitoring, measurement, environment";
        $this->data["institutions"] = $institutions;
        $this->data["markersData"] = $markersData;
        $this->data["newLocalityId"] = $mapMng->getNewLocalityId();
        $this->data["searchResults"] = $mapMng->getSearchResults();
        $this->view = "map";
    }
}
