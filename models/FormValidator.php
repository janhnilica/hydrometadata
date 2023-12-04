<?php
class FormValidator
{
    /**
     * array of particular FormValidationItem objects
     * @var array
     */
    private array $items = [];
    
    /**
     * array of user messages
     * @var array
     */
    public array $errorMessages = [];
    
    /**
     * adds an item
     * @param FormValidatorItem $item
     * @return void
     */
    public function addItem(FormValidatorItem $item): void
    {
        $this->items[] = $item;
    }
    
    /**
     * creates a regex pattern for identification of a multiple-input key
     * @param string $key - containing #
     * @return string
     */
    private function createRegex(string $key): string
    {
        return "/" . str_replace("#", ".+", $key) . "/";
    }
    
    /**
     * tests a form input key against given regex pattern
     * @param string $key - form input name
     * @param string $regex - regex pattern for a multiple key
     * @return bool
     */
    private function matchMultipleKey(string $key, string $regex): bool
    {
        $match = preg_match($regex, $key);
        if ($match === 1)
        {
            $rest = preg_replace($regex, "", $key);
            if (mb_strlen($rest) > 0)
                return false;
            return true;
        }
        return false;
    }
    
    /**
     * returns data from POST, trimmed and restricted to keys from this->items
     * @param array $keys
     * @return array
     */
    public function extractFormData(): array
    {
        $formData = [];
        $postKeys = array_keys($_POST);
        foreach ($this->items as $item)
        {
            if (mb_strpos($item->key, "#") !== false) // multiple
            {
                $regex = $this->createRegex($item->key);
                foreach ($postKeys as $pKey)
                {
                    if ($this->matchMultipleKey($pKey, $regex))
                        $formData[$pKey] = trim(strval($_POST[$pKey]));
                }
            }
            else
            {
                if (array_key_exists($item->key, $_POST))
                    $formData[$item->key] = trim(strval($_POST[$item->key]));
            }
        }
        return $formData;
    }
    
    /**
     * performs all conversions and tests of all individual items
     * multiple items does not have to occur in form data
     * @param array $formData - associative array of form data 
     * @return bool
     * @throws \Exception
     * @throws UserException
     */
    public function validate(array & $formData): bool
    {
        $result = true;
        
        foreach ($this->items as $item)
        {
            if (mb_strpos($item->key, "#") !== false) // multiple key item
            {
                $regex = $this->createRegex($item->key);
                $formKeys = array_keys($formData);
                foreach ($formKeys as $key)
                {
                    if ($this->matchMultipleKey($key, $regex))
                    {
                        try { $itemResult = $item->test($formData[$key]); }
                        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
                        
                        if (!$itemResult)
                        {
                            $result = false;
                            foreach ($item->errorMessages as $msg) // avoid identical error messages
                            {
                                if (!in_array($msg, $this->errorMessages))
                                    $this->errorMessages[] = $msg;
                            }
                        }
                    }
                }
            }
            else // standard (single-key) item
            {
                if (!array_key_exists($item->key, $formData))
                    throw new UserException("Item '$item->referenceName' not found in form");
                
                try { $itemResult = $item->test($formData[$item->key]); }
                catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
                
                if (!$itemResult)
                {
                    $result = false;
                    $this->errorMessages = array_merge($this->errorMessages, $item->errorMessages);
                }
            }
        }
        
        return $result;
    }
}
