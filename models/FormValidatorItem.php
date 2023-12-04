<?php
class FormValidatorItem
{
    /**
     * form input name
     * name containing "#"
     * - is considered as multiple (may occure several times in the form)
     * - "#" is a placeholder (each multiple input has a specific string instead of "#")
     * @var string
     */
    public string $key;
    
    /**
     * name used in an error message
     * @var string
     */
    public string $referenceName;
    
    /**
     * determines whether a tested value can be empty string
     * @var bool
     */
    private bool $canBeEmpty = false;
    
    /**
     * determines whether a tested value can be null
     * @var bool
     */
    private bool $canBeNull = false;
    
    /**
     * if not empty, contains "from" and "to" keys, determining a conversion parameters
     * if a tested value equals "from", it is converted (before validation)
     * @var array
     */
    private array $conversion = [];
    
    /**
     * set of rules
     * a rule is an associative array, containing rule parameters
     * always contains:
     *  - "method" key: contains a name of testing method
     *  - "errorMessage" key: contains a message associated with the rule violation
     * further parameters depend on a particular rule
     * @var array
     */
    private array $rules = [];
    
    /**
     * messages for the user
     * @var array
     */
    public array $errorMessages = [];
    
    /**
     * constructor
     * @param string $key - form input name
     * @param string $referenceName - name used in an error message
     */
    public function __construct(string $key, string $referenceName)
    {
        $this->key = $key;
        $this->referenceName = $referenceName;
    }
    
    /**
     * sets the conversion parameters
     * @param mixed $from
     * @param mixed $to
     * @return FormValidatorItem
     */
    public function setConversion(mixed $from, mixed $to): FormValidatorItem
    {
        $this->conversion["from"] = $from;
        $this->conversion["to"] = $to;
        return $this;
    }
    
    //////////////////
    // adding rules //
    //////////////////
    /**
     * allows a tested value to be an empty string
     * @return FormValidatorItem
     */
    public function addRuleCanBeEmpty(): FormValidatorItem
    {
        $this->canBeEmpty = true;
        return $this;
    }
    
    /**
     * allows a tested value to be null
     * @return FormValidatorItem
     */
    public function addRuleCanBeNull(): FormValidatorItem
    {
        $this->canBeNull = true;
        return $this;
    }
    
    /**
     * adds non-emty rule (input must not be empty)
     * @return FormValidatorItem
     */
    public function addRuleIsNotEmpty(): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isNotEmptyTest",
            "errorMessage" => "value must not be empty"];
        return $this;
    }
    
    /**
     * adds is-int rule (input must be a valid int)
     * @return FormValidatorItem
     */
    public function addRuleIsInt(): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isIntTest",
            "errorMessage" => "value must be an integer"];
        return $this;
    }
    
    /**
     * adds is-float rule (input must be a valid float)
     * @return FormValidatorItem
     */
    public function addRuleIsFloat(): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isFloatTest",
            "errorMessage" => "value must be a valid floating point number"];
        return $this;
    }
    
    /**
     * adds is-larger-than rule (input must be number larger than given value)
     * @param float $value
     * @return FormValidatorItem
     */
    public function addRuleIsLargerThan(float $value): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isLargerThanTest",
            "errorMessage" => "value must be larger than $value",
            "parameter" => $value];
        return $this;
    }
    
    /**
     * adds is-lower-than rule (input must be lower than given value)
     * @param float $value
     * @return FormValidatorItem
     */
    public function addRuleIsLowerThan(float $value): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isLowerThanTest",
            "errorMessage" => "value must be lower than $value",
            "parameter" => $value];
        return $this;
    }
    
    /**
     * adds unique rule (input must not occur in given column of given table)
     * @param string $table
     * @param string $column
     * @return FormValidatorItem
     */
    public function addRuleIsUnique(string $table, string $column): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isUniqueTest",
            "errorMessage" => "an item with this value already exists",
            "table" => $table,
            "column" => $column];
        return $this;
    }
    
    /**
     * adds unique except rule
     * (input must not occur in given column of given table except the rows where exceptColumnName has exceptColumnValue)
     * @param string $table
     * @param string $column
     * @param string $exceptColumnName
     * @param string $exceptColumnValue
     * @return FormValidatorItem
     */
    public function addRuleIsUniqueExcept(string $table, string $column, string $exceptColumnName, string $exceptColumnValue): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isUniqueExceptTest",
            "errorMessage" => "another item with this value already exists",
            "table" => $table,
            "column" => $column,
            "exceptColumnName" => $exceptColumnName,
            "exceptColumnValue" => $exceptColumnValue];
        return $this;
    }
    
    /**
     * adds is-key-from-table rule
     * (input must be a valid key from given table)
     * @param string $table
     * @param string $keyColumn
     * @return FormValidatorItem
     */
    public function addRuleIsKeyFromTable(string $table, string $keyColumn): FormValidatorItem
    {
        $this->rules[] = [
            "method" => "isKeyFromTableTest",
            "errorMessage" => "invalid value",
            "table" => $table,
            "keyColumn" => $keyColumn];
        return $this;
    }
    
    
    /////////////////////
    // testing methods //
    /////////////////////
    /**
     * performs non-empty test
     * @param array $params
     * @return bool
     */
    private function isNotEmptyTest(array $params): bool
    {
        if (mb_strlen($params["value"]) === 0)
            return false;
        return true;
    }
    
    /**
     * performs is-int test
     * @param array $params
     * @return bool
     */
    private function isIntTest(array $params): bool
    {
        $value = $params["value"];
        if (mb_substr($value, 0, 1)  === "-")
            $value = mb_substr($value, 1);
        return ctype_digit($value);
    }
    
    /**
     * performs is-float test
     * @param array $params
     * @return bool
     */
    private function isFloatTest(array $params): bool
    {
        return is_numeric($params["value"]);
    }
    
    /**
     * performs larger-than test
     * @param array $params
     * @return bool
     */
    private function isLargerThanTest(array $params): bool
    {
        return (is_numeric($params["value"]) && floatval($params["value"]) > $params["parameter"]);
    }
    
    /**
     * performs lower-than test
     * @param array $params
     * @return bool
     */
    private function isLowerThanTest(array $params): bool
    {
        return (is_numeric($params["value"]) && floatval($params["value"]) < $params["parameter"]);
    }
    
    /**
     * performs unique test
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    private function isUniqueTest(array $params): bool
    {
        try {
            $count = Dtb::getSingleValue("SELECT COUNT(*) FROM `" . $params["table"] . "` WHERE `" . $params["column"] . "` = ?;", $params["value"]);
            return $count === 0;
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * pereforms unique-except test
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    private function isUniqueExceptTest(array $params): bool
    {
        try {
            $count = Dtb::getSingleValue("SELECT COUNT(*) FROM `" . $params["table"] . "` "
                    . "WHERE `" . $params["column"] . "` = ? "
                    . "AND " . $params["exceptColumnName"] . " != ?;", $params["value"], $params["exceptColumnValue"]);
            return $count === 0;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * performs is-key-from-table test
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    private function isKeyFromTableTest(array $params): bool
    {
        try {
            $count = Dtb::getSingleValue("SELECT COUNT(*) FROM `" . $params["table"] . "` WHERE `" . $params["keyColumn"] . "` = ?;", $params["value"]);
            return $count > 0;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    ///////////////////////
    // testing all rules //
    ///////////////////////
    /**
     * performs the conversion (if any) and all tests
     * all particular error messages are appended to the central array
     * @param string $value - a value from a form input
     * @return bool
     */
    public function test(string & $value): bool
    {
        if (!empty($this->conversion))
        {
            if ($value == $this->conversion["from"])
                $value = $this->conversion["to"];
        }
        
        if ($this->canBeEmpty)
        {
            if ($value === "")
                return true;
        }
        
        if ($this->canBeNull)
        {
            if ($value === null)
                return true;
        }
        
        $result = true;
        foreach ($this->rules as $rule)
        {
            $rule["value"] = $value;
            if (!$this->{$rule["method"]}($rule))
            {
                $result = false;
                $this->errorMessages[] = $this->referenceName . ": " . $rule["errorMessage"];
            }
        }
        
        return $result;
    }
}
