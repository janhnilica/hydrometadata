<?php
abstract class Controller
{
    protected array $data = array();
    protected string $view = "";
    protected array $header = array(
        "title" => "",
        "keywords" => "",
        "description" => "");
    
    /**
     * abstract method, each controller has its own implementation
     */
    abstract function process(array $parameters): void;
    
    /**
     * verifies whether a user is logged in (or whether is an admin)
     * @param bool $admin - is admin required
     * @return bool
     */
    public function verifyUser(bool $admin = false): bool
    {
        $manager = new UserManager();
        $user = $manager->getLoggedUser();
        if (!$user || ($admin && !$user['admin']))
            return false;
        return true;
    }
    
    /**
     * inserts a user message to SESSION
     * @param string $message
     * @param string $class
     * @return void
     */
    public function addMessage(string $message, string $class = "info-message"):void
    {
        if (isset($_SESSION["messages"]))
            $_SESSION["messages"][] = ["message" => $message, "class" => $class];
        else
            $_SESSION["messages"] = [["message" => $message, "class" => $class]];
    }
    
    /**
     * returns an array of user messages from SESSION
     * deletes the array from SESSION
     * @return array
     */
    public function getMessages(): array
    {
        if (isset($_SESSION['messages']))
        {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            return $messages;
        }
        else
            return [];
    }
    
    /**
     * treats data with htmlspecialchars
     * @param mixed $data
     * @return type
     */
    public function escapeData($data = null)
    {
        if (!isset($data))
            return null;
        elseif (is_string($data))
            return htmlspecialchars($data, ENT_QUOTES);
        elseif (is_array($data))
        {
            foreach($data as $key => $value)
                $data[$key] = $this->escapeData($value);
            return $data;
        }
        else
            return $data;
    }
    
    /**
     * extracts the data into variables and includes the view
     * @return void
     */
    public function renderView(): void
    {
        if ($this->view)
        {
            extract($this->escapeData($this->data));
            //extract($this->data, EXTR_PREFIX_ALL, "");
            require("views/" . $this->view . ".phtml");
        }
    }
    
    /**
     * redirects to given URL and terminates the script
     * @param string $url
     * @return \never
     */
    public function redirect(string $url): never
    {
        header("Location: /$url");
        header("Connection: close");
        exit();
    }
    
    /**
     * creates a title in the form 'page | website'
     * @param string $page
     * @return string
     */
    public function createTitle(string $page): string
    {
        return $page . " | " . WEBSITE_NAME;
    }
    
    //////////////////
    // VIEW METHODS //
    //////////////////
    /**
     * prints a value if its key exists in array (formData)
     * @param array $array
     * @param string $key
     */
    public function printValueIfExists(array $array, string $key)
    {
        if (array_key_exists($key, $array))
            echo($array[$key]);
    }
    
    /**
     * prints select option from given dtb table
     * @param array $table
     * @param string $columnValues - name of table column
     * @param string $columnLabels - name of table column
     * @param mixed $selectedValue
     * @param bool $noneItem - whether to add the first "none" item with value -1
     * @param mixed $noneItemValue
     * @param string $noneItemLabel
     * @return void
     */
    public function printSelectOptions(
            array $table,
            string $columnValues,
            string $columnLabels,
            mixed $selectedValue = null,
            bool $noneItem = false,
            mixed $noneItemValue = -1,
            string $noneItemLabel = "None"): void
    {
        $html = "";
        
        if ($noneItem)
            $html .= '<option value="' . $noneItemValue . '">' . $noneItemLabel . '</option>';
        
        foreach ($table as $row)
        {
            if ($row[$columnValues] == $selectedValue)
                $html .= '<option value="' . $row[$columnValues] . '" selected>' . $row[$columnLabels] . '</option>';
            else
                $html .= '<option value="' . $row[$columnValues] . '">' . $row[$columnLabels] . '</option>';
        }
        
        echo($html);
    }
    
    /**
     * prints checkboxes from given dtb table
     * name and id attributes = prefix + table-value
     * value attribute = table-value
     * checkbox label = table-label
     * @param array $table
     * @param string $columnValues - name of table column
     * @param string $columnLabels - name of table column
     * @param string $prefix - used for name and id checkbox attributes
     * @param array $formData
     * @return void
     */
    public function printCheckboxes(array $table, string $columnValues, string $columnLabels, string $prefix = "", array $formData = []): void
    {
        $html = "";
        
        foreach ($table as $row)
        {
            $prefValue = $prefix . $row[$columnValues];
            $html .= '<input type="checkbox" id="' . $prefValue . '" name="' . $prefValue . '" value="' . $row[$columnValues] .'"';
            if (key_exists($prefValue, $formData))
                $html .= ' checked>';
            else
                $html .= '>';
            $html .= '<label for="' . $prefValue . '">' . $row[$columnLabels] . '</label><br>';
        }
        echo($html);
    }
    
    //////////////////
    // FORM METHODS //
    //////////////////
    /**
     * returns form data: 1. from sessison / 2. from dtb / 3. empty array
     * @param string $table - dtb table
     * @param string $idColumn - name of dtb column
     * @param string $idValue - name of dtb column
     * @return array
     * @throws \Exception
     */
    function getInitialFormData(string $table = null, string $idColumn = null, string $idValue = null): array
    {
        if (isset($_SESSION["formData"]))
        {
            $formData = $_SESSION["formData"];
            unset($_SESSION["formData"]);
            return $formData;
        }
        elseif ($table !== null)
        {
            try { return Dtb::getOneRow("SELECT * FROM `$table` WHERE `$idColumn` = ?;", $idValue); }
            catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
        }
        else
            return [];
    }
    
    /**
     * performs validation, in case of:
     *  - validation fail -> adds messages, redirects to the same page
     *  - UserException (input not found) -> adds message, redirects to the same page
     *  - \Exception (error) -> adds messages, redirect to 500 error page
     * @param string $thisUrl - url of the processed form
     * @param array $formData - trimmed, extracted
     * @param FormValidator $validator
     * @return void
     */
    public function performValidation(string $thisUrl, array & $formData, FormValidator $validator): void
    {
        try { $validationResult = $validator->validate($formData); }
        catch (UserException $ex)
        {
            $this->addMessage($ex->getMessage(), "warning-message");
            $validationResult = false;
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        if (!$validationResult)
        {
            foreach ($validator->errorMessages as $msg)
                $this->addMessage($msg, "warning-message");
            $_SESSION["formData"] = $formData;
            $this->redirect($thisUrl);
        }
    }
    
    
    
    
}
