<?php
class LocalityController extends Controller
{
    public function process(array $params): void
    {
        $localityMng = new LocalityManager();
        $landcoverMng = new LandcoverManager();
        $monitoringMng = new MonitoringManager();
        $imagesMng = new ImagesManager();
        $userMng = new UserManager();
        $user = $userMng->getLoggedUser();
        
        try
        {
            $slopes = $localityMng->getSlopes();
            $expositions = $localityMng->getExpositions();
            $soilTypes = $localityMng->getSoilTypes();
            $soilTextures = $localityMng->getSoilTextures();
            $landcovers = $landcoverMng->getLandcovers();
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        $validator = $localityMng->getLocalityValidator();
        
        // new
        if (count($params) === 1 && $params[0] === "new")
        {
            if (!$user)
                $this->redirect(ERR_UNAUTHORIZED);
            
            $formData = $this->getInitialFormData();
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("locality/new", $formData, $validator);
                $formData["id_instituce"] = $user["id_instituce"];
                
                try
                {
                    Dtb::insert("lokality", $formData);
                    $insertedId = Dtb::getLastId();
                }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Creation of locality " . $formData["nazev"]);
                $this->addMessage("Locality has been created. Go to locality detail to add monitored variables.", "info-message");
                $_SESSION["newLocalityId"] = $insertedId;
                $this->redirect("map");
            }
            
            $this->data["action"] = "new";
            $this->data["formData"] = $formData;
            $this->data["slopes"] = $slopes;
            $this->data["expositions"] = $expositions;
            $this->data["soilTypes"] = $soilTypes;
            $this->data["soilTextures"] = $soilTextures;
            $this->data["landcovers"] = $landcovers ;
            $this->header["title"] = $this->createTitle("New locality");
            $this->view = "locality";
        }
        
        // edit
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "edit")
        {
            $id = intval($params[0]);
            
            if (!$user)
                $this->redirect(ERR_UNAUTHORIZED);

            try
            {
                $locality = $localityMng->getLocality($id);
                $formData = $this->getInitialFormData("lokality", "id_lokalita", $id);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if ($locality === [] || $formData === [])
                $this->redirect(ERR_NOTFOUND);
            
            if ($locality["id_instituce"] != $user["id_instituce"])
                $this->redirect(ERR_FORBIDDEN);
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("locality/$id/edit", $formData, $validator);
                
                try { Dtb::update("lokality", $formData, "WHERE `id_lokalita` = ?;", [$id]); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Update of locality " . $formData["nazev"]);
                $this->addMessage("Locality has been updated.", "info-message");
                $this->redirect("locality/$id");
            }
            
            $this->data["action"] = "edit";
            $this->data["formData"] = $formData;
            $this->data["slopes"] = $slopes;
            $this->data["expositions"] = $expositions;
            $this->data["soilTypes"] = $soilTypes;
            $this->data["soilTextures"] = $soilTextures;
            $this->data["landcovers"] = $landcovers ;
            $this->header["title"] = $this->createTitle("Locality editation");
            $this->view = "locality";
        }
        
        // detail
        elseif (count($params) === 1 && ctype_digit($params[0]))
        {
            $id = intval($params[0]);
            try
            {
                $locality = $localityMng->getLocalityPresentation($id);
                $monitoring = $monitoringMng->getLocalityMonitoring($id);
                $images = $imagesMng->getListOfLocalityImages($id);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if (!$user)
                $this->data["showEditPanel"] = false;
            else
                $this->data["showEditPanel"] = $locality["id_instituce"] == $user["id_instituce"];
            $this->data["locality"] = $locality;
            $this->data["monitoring"] = $monitoring;
            $this->data["pathToDir"] = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
            $this->data["images"] = $images["names"];
            $this->header["title"] = $this->createTitle("Locality detail");
            $this->view = "locality-detail";
        }
        
        // delete
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "delete")
        {
            $id = intval($params[0]);
            
            if (!$user)
                $this->redirect(ERR_UNAUTHORIZED);

            try { $locality = $localityMng->getLocality($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if ($locality === [])
                $this->redirect(ERR_NOTFOUND);
            
            if ($locality["id_instituce"] != $user["id_instituce"])
                $this->redirect(ERR_FORBIDDEN);
            
            try
            {
                $localityMng->deleteLocality($id);
                $imagesMng->deleteLocalityImages($id);
            }
            catch (UserException $ex)
            {
                logException($ex);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $userMng->saveUserActivity("Deletion of locality " . $locality["nazev"]);
            $this->addMessage("Locality has been deleted.", "info-message");
            $this->redirect("map");
        }
        
        // incorrect url
        else
            $this-$this->redirect(ERR_NOTFOUND);
    }
}
