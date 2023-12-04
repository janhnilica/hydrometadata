<?php
class VariablesManager
{
    /**
     * returns all variables from dtb
     * @return array
     * @throws \Exception
     */
    public function getVariables(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `veliciny` ORDER BY `nazev` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * creates and returns a validator for variable creation
     * @return FormValidator
     */
    public function getVariableValidator(): FormValidator
    {
        $validator = new FormValidator();
        
        $item = new FormValidatorItem("nazev", "Name");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUnique("veliciny", "nazev");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("poznamka", "Comment");
        $item->setConversion("", null)
                ->addRuleCanBeNull();
        $validator->addItem($item);
        
        return $validator;
    }

    /**
     * saves new variable into dtb
     * @param array $formData
     * @return void
     * @throws \Exception
     */
    public function saveVariable(array $formData): void
    {
        try {
            Dtb::insert("veliciny", $formData);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
