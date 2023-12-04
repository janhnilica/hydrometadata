<?php
class LandcoverManager
{
    /**
     * returns complete landcovers dtb table ordered by `nazev`
     * @return array
     * @throws \Exception
     */
    public function getLandcovers(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `landcovery` ORDER BY `nazev`;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns a dtb record of a landcover with given id
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getLandcover(int $id): array
    {
        try {
            return Dtb::getOneRow("SELECT * FROM `landcovery` WHERE `id_landcover` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * creates and returns a validator for landcover creation
     * @return FormValidator
     */
    public function getLandcoverValidator(): FormValidator
    {
        $validator = new FormValidator();
        
        $item = new FormValidatorItem("nazev", "Name");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUnique("landcovery", "nazev");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("poznamka", "Comment");
        $item->setConversion("", null)
                ->addRuleCanBeNull();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * saves new landcover into dtb
     * @param array $formData
     * @return void
     * @throws \Exception
     */
    public function saveLandcover(array $formData): void
    {
        try {
            Dtb::insert("landcovery", $formData);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}


