<?php
class UserController extends Controller
{
    public function process(array $params): void
    {
        if (!$this->verifyUser(true))
            $this->redirect(ERR_FORBIDDEN);
        
        $userMng = new UserManager();
        $instMng = new InstitutionManager();
        $actMng = new ActivitiesManager();
        
        // new
        if (count($params) === 1 && $params[0] === "new")
        {
            $validator = $userMng->getNewUserValidator();
            
            $formData = $this->getInitialFormData();
            try { $institutions = $instMng->getInstitutions(); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("user/new", $formData, $validator);
                $formData["heslo"] = $userMng->getHash($formData["heslo"]);
                
                try { Dtb::insert("uzivatele", $formData); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Creation of user " . $formData["jmeno"]);
                $this->addMessage("User has been created", "info-message");
                $this->redirect("admin");
            }
            
            $this->data["formData"] = $formData;
            $this->data["institutions"] = $institutions;
            $this->header["title"] = $this->createTitle("New user");
            $this->view = "user-new";
        }
        
        // detail
        elseif (count($params) === 1 && ctype_digit($params[0]))
        {
            $id = $params[0];
            try
            {
                $user = $userMng->getUser($id);
                if (!$user)
                    $this->redirect(ERR_NOTFOUND);
                $instName = $instMng->getInstitutionName($user["id_instituce"]);
                $activities = $actMng->getUserActivities($id);
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->header["title"] = $this->createTitle("User detail");
            $this->data["user"] = $user;
            $this->data["institution"] = $instName;
            $this->data["activities"] = $activities;
            $this->view = "user-detail";
        }
        
        // edit
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "edit")
        {
            $id = $params[0];
            try
            {
                $user = $userMng->getUser($id);
                if (!$user)
                    $this->redirect(ERR_NOTFOUND);
                $formData = $this->getInitialFormData("uzivatele", "id_uzivatel", $id);
                $institutions = $instMng->getInstitutions();
            }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $validator = $userMng->getEditUserValidator($id);
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("user/$id/edit", $formData, $validator);
                
                if ($formData["heslo"] === "")
                    unset($formData["heslo"]);
                else
                    $formData["heslo"] = $userMng->getHash($formData["heslo"]);
                
                try { $userMng->updateUser($id, $formData); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Editation of user " . $formData["jmeno"]);
                $this->addMessage("User has been updated", "info-message");
                $this->redirect("user/$id");
            }
            
            $this->data["formData"] = $formData;
            $this->data["institutions"] = $institutions;
            $this->header["title"] = $this->createTitle("User editation");
            $this->view = "user-edit";
        }

        // delete
        elseif (count($params) === 2 && ctype_digit($params[0]) && $params[1] === "delete")
        {
            $id = $params[0];
            try { $user = $userMng->getUser($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            if (!$user)
                $this->redirect(ERR_NOTFOUND);
            
            $logged = $userMng->getLoggedUser();
            if ($logged["id_uzivatel"] === $user["id_uzivatel"])
            {
                $this->addMessage("Trying to delete yourself, this is forbidden! Suicide is a sin!", "warning-message");
                $this->redirect("user/$id");
            }
            
            try { $userMng->deleteUser($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $userMng->saveUserActivity("Deletion of user " . $user["jmeno"]);
            $this->addMessage("User has been deleted", "info-message");
            $this->redirect("admin");
        }
        
        // activities delete
        elseif (count($params) === 3 && ctype_digit($params[0]) && $params[1] === "activities" && $params[2] === "delete")
        {
            $id = $params[0];
            try { $user = $userMng->getUser($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            if (!$user)
                $this->redirect(ERR_NOTFOUND);
            
            try { $actMng->deleteUserActivities($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $userMng->saveUserActivity("Deletion of activities of " . $user["jmeno"]);
            $this->addMessage("Activities have been deleted", "info-message");
            $this->redirect("user/$id");
        }
        
        // incorrect url
        else
            $this->redirect(ERR_NOTFOUND);
    }
}




























