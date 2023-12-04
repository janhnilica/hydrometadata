<?php
class AdminController extends Controller
{
    public function process(array $params): void
    {
        if (count($params) > 0)
            $this->redirect(ERR_NOTFOUND);
        
        if (!$this->verifyUser(true))
            $this->redirect(ERR_FORBIDDEN);
        
        $institutionManager = new InstitutionManager();
        $userManager = new UserManager();
        
        try
        {
            $users = $userManager->getUsersSignatures();
            $institutions = $institutionManager->getInstitutionsSignatures();
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        try { $errlog = file(ERRLOG_FILE); }
        catch (\Exception $ex)
        {
            $errlog = [];
            $this->addMessage("Errlog file not found", "warning-message");
        }
        
        if (count($errlog) === 0)
        {
            $this->data["errors"] = false;
            $errlog = ["No errors logged"];
        }
        else
            $this->data["errors"] = true;
        
        $this->header["title"] = $this->createTitle("Admin dashboard");
        $this->data["institutions"] = $institutions;
        $this->data["users"] = $users;
        $this->data["errlog"] = $errlog;
        $this->view = "admin";
    }
}

