<?php
class SearchController extends Controller
{
    public function process(array $params): void
    {
        $variablesMng = new VariablesManager();
        $monitoringMng = new MonitoringManager();
        $localityMng = new LocalityManager();
        $landcoverMng = new LandcoverManager();
        $searchMng = new SearchManager();
        
        if (count($params) === 0)
        {
            try
            {
                $variables = $variablesMng->getVariables();
                $soilTypes = $localityMng->getSoilTypes();
                $soilTextures = $localityMng->getSoilTextures();
                $landcovers = $landcoverMng->getLandcovers();
                $slopes = $localityMng->getSlopes();
                $expositions = $localityMng->getExpositions();
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $validator = $searchMng->getSearchValidator();
            $formData = $this->getInitialFormData();
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("search", $formData, $validator);
                
                try { $resultList = $searchMng->search($formData); }
                catch (Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $n = count($resultList);
                if ($n === 0)
                {
                    $this->addMessage("No locality satisfying the criteria was found.", "warning-message");
                    $this->redirect("search");
                }
                else
                {
                    $resultsId = [];
                    foreach ($resultList as $resultItem)
                        $resultsId[] = $resultItem["id_lokalita"];
                    $_SESSION["search-results"] = $resultsId;
                    $this->redirect("map");
                }
            }
            
            $this->data["formData"] = $formData;
            $this->data["variables"] = $variables;
            $this->data["fromTable"] = $monitoringMng->getTimeSelectTable(false);
            $this->data["toTable"] = $monitoringMng->getTimeSelectTable(true);
            $this->data["soilTypes"] = $soilTypes;
            $this->data["soilTextures"] = $soilTextures;
            $this->data["landcovers"] = $landcovers;
            $this->data["slopes"] = $slopes;
            $this->data["expositions"] = $expositions;
            $this->header["title"] = $this->createTitle("Search");
            $this->view = "search";
        }
        
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
