<?php
class AccountController extends Controller
{
    public function process(array $params): void
    {
        if (count($params) > 0)
            $this->redirect(ERR_NOTFOUND);
        
        $userManager = new UserManager();
        $user = $userManager->getLoggedUser();
        
        if (!$user)
            $this->redirect(ERR_UNAUTHORIZED);
        
        $institutionManager = new InstitutionManager();
        try { $institution = $institutionManager->getInstitutionName($user["id_instituce"]); }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        $validator = new FormValidator();
        $validator->addItem(new FormValidatorItem("password", "Password"));
        $validator->addItem(new FormValidatorItem("password2", "Repeated password"));
        
        if ($_POST)
        {
            $formData = $validator->extractFormData();
            $this->performValidation("account", $formData, $validator);
            
            $pswd = trim($formData["password"]);
            if (mb_strlen($pswd) > 0 && $pswd === $formData["password2"])
            {
                try
                {
                    $userManager->updateUserPassword($user["id_uzivatel"], $pswd);
                    $userManager->saveUserActivity("password change");
                    $this->addMessage("Password has been updated");
                    $this->redirect("account");
                }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
            }
            else
            {
                $this->addMessage("Passwords must be identical and must not be empty", "warning-message");
                $this->redirect("account");
            }
        }

        $this->header["title"] = $this->createTitle("My account");
        $this->data["name"] = $user["jmeno"];
        $this->data["email"] = $user["email"];
        $this->data["institution"] = $institution;
        $this->view = "account";
    }
}
