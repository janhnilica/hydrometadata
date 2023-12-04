<?php
class LandcoverController extends Controller
{
    public function process(array $params): void
    {
        $landcoverMng = new LandcoverManager();
        $userMng = new UserManager();
        
        $user = $userMng->getLoggedUser();
        if (!$user)
            $this->redirect(ERR_UNAUTHORIZED);
        
        if (count($params) === 1 && $params[0] === "new")
        {
            $validator = $landcoverMng->getLandcoverValidator();
            $formData = $this->getInitialFormData();

            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("landcover/new", $formData, $validator);
                
                try { $landcoverMng->saveLandcover($formData); }
                catch (Exception $ex)
                {
                    logException($ex);
                    $this-$this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Creation of landcover " . $formData["nazev"]);
                $this->addMessage("Landcover has been created", "info-message");
                $this->redirect("table/landcovers");
            }
            
            $this->header["title"] = $this->createTitle("New landcover");
            $this->data["formData"] = $formData;
            $this->view = "landcover-new";
        }
        
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
