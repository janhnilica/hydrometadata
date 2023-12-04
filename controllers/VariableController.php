<?php
class VariableController extends Controller
{
    public function process(array $params): void
    {
        $variablesMng = new VariablesManager();
        $userMng = new UserManager();
        
        $user = $userMng->getLoggedUser();
        if (!$user)
            $this->redirect(ERR_UNAUTHORIZED);

        if (count($params) === 1 && $params[0] === "new")
        {
            $validator = $variablesMng->getVariableValidator();
            $formData = $this->getInitialFormData();

            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("variable/new", $formData, $validator);
                
                try { $variablesMng->saveVariable($formData); }
                catch (Exception $ex)
                {
                    logException($ex);
                    $this-$this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Creation of variable " . $formData["nazev"]);
                $this->addMessage("Variable has been created", "info-message");
                $this->redirect("table/variables");
            }
            
            $this->header["title"] = $this->createTitle("New variable");
            $this->data["formData"] = $formData;
            $this->view = "variable-new";
        }
        
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
