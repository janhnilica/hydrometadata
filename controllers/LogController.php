<?php
class LogController extends Controller
{
    public function process(array $params): void
    {
        $userManager = new UserManager();
        
        // login
        if (count($params) === 1 && $params[0] === "in")
        {
            if ($userManager->getLoggedUser())
                $this->redirect(HOMEPAGE);
            
            $validator = new FormValidator();
            $item = new FormValidatorItem("email", "E-mail");
            $item->addRuleIsNotEmpty();
            $validator->addItem($item);
            
            $item = new FormValidatorItem("password", "Password");
            $item->addRuleIsNotEmpty();
            $validator->addItem($item);
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("log/in", $formData, $validator);
                
                try
                {
                    $userManager->login($formData["email"], $formData["password"]);
                    $userManager->saveUserActivity("login");
                    session_regenerate_id();
                    $this->addMessage("Successfully logged in", "log-message");
                    $this->redirect(HOMEPAGE);
                }
                catch (UserException $ex)
                {
                    $this->addMessage($ex->getMessage(), "warning-message");
                    $_SESSION["email"] = $_POST["email"];
                    $this->redirect("log/in");
                }
                catch(\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
            }

            $this->data["email"] = "";
            if (isset($_SESSION["email"]))
            {
                $this->data["email"] = $_SESSION["email"];
                unset($_SESSION["email"]);
            }
            $this->header["title"] = $this->createTitle("Login");
            $this->view = "login";
        }
        
        // logout
        elseif (count($params) === 1 && $params[0] === "out")
        {
            if (!$userManager->getLoggedUser())
                $this->redirect(HOMEPAGE);
            $userManager->saveUserActivity("logout");
            $userManager->logout();
            session_regenerate_id();
            $this->addMessage("Successfully logged out", "log-message");
            $this->redirect(HOMEPAGE);
        }
        
        // incorrect url
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
