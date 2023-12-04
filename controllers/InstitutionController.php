<?php
class InstitutionController extends Controller
{
    public function process(array $params): void
    {
        if (!$this->verifyUser(true))
            $this->redirect(ERR_FORBIDDEN);
        
        $instMng = new InstitutionManager();
        $userMng = new UserManager();
        
        // new
        if (count($params) === 1 && $params[0] === "new")
        {
            $formData = $this->getInitialFormData();
            $validator = $instMng->getNewInstitutionValidator();
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("institution/new", $formData, $validator);
                
                try { Dtb::insert("instituce", $formData); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Creation of institution " . $formData["nazev"]);
                $this->addMessage("Institution has been created", "info-message");
                $this->redirect("admin");
            }
            
            $this->data["formData"] = $formData;
            $this->header["title"] = $this->createTitle("New institution");
            $this->view = "institution-new";
        }
        
        // detail
        elseif (count($params) === 1 && ctype_digit($params[0]))
        {
            $id = $params[0];
            try
            {
                $institution = $instMng->getInstitution($id);
                if (!$institution)
                    $this->redirect(ERR_NOTFOUND);
                
                if ($institution["id_kontaktni_uzivatel"])
                    $contactUser = $userMng->getUser($institution["id_kontaktni_uzivatel"]);
                else
                    $contactUser = null;
                
                $instUsers = $instMng->getInstitutionUsers($id);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->header["title"] = $this->createTitle("Institution detail");
            $this->data["institution"] = $institution;
            $this->data["contactUser"] = $contactUser;
            $this->data["users"] = $instUsers;
            $this->view = "institution-detail";
        }
        
        // edit
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "edit")
        {
            $id = $params[0];
            try
            {
                $formData = $this->getInitialFormData("instituce", "id_instituce", $id);
                if (!$formData)
                    $this->redirect(ERR_NOTFOUND);
                $instUsers = $instMng->getInstitutionUsers($id);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $validator = $instMng->getEditInstitutionValidator($id);
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("institution/$id/edit", $formData, $validator);
                
                try { Dtb::update("instituce", $formData, "WHERE `id_instituce` = ?", [$id]); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Update of institution " . $formData["nazev"]);
                $this->addMessage("Institution has been updated", "info-message");
                $this->redirect("institution/$id");
            }
            
            $this->header["title"] = $this->createTitle("Institution edit");
            $this->data["formData"] = $formData;
            $this->data["users"] = $instUsers;
            $this->view = "institution-edit";
        }
        
        // delete
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "delete")
        {
            $id = $params[0];
            try { $institution = $instMng->getInstitution($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if (!$institution)
                $this->redirect(ERR_NOTFOUND);
            
            try { $instMng->deleteInstitution($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $userMng->saveUserActivity("Deletion of institution " . $institution["nazev"]);
            $this->addMessage("Institution has been deleted", "info-message");
            $this->redirect("admin");
        }
        
        // incorrect ulr
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
