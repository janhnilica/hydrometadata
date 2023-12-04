<?php
class ErrlogController extends Controller
{
    public function process(array $params): void
    {
        if (!$this->verifyUser(true))
            $this->redirect(ERR_FORBIDDEN);
        
        if (count($params) === 1 && $params[0] === "delete")
        {
            if (is_file(ERRLOG_FILE))
            {
                if (file_put_contents(ERRLOG_FILE, "") === false)
                    $this->addMessage ("Cannot clear the errlog file", "warning-message");
                else
                {
                    $userMng = new UserManager();
                    $userMng->saveUserActivity("Errlog file content deletion");
                    $this->addMessage ("The errlog file content has been deleted", "info-message");
                }
            }
            else
                $this->addMessage ("Cannot find the errlog file", "warning-message");
            
            $this->redirect("admin");
        }
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
