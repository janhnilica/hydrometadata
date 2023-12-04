<?php
class InstitutionManager
{
    /**
     * returns signatures (names and ids) of institutions
     * @return array
     * @throws \Exception
     */
    public function getInstitutionsSignatures(): array
    {
        try {
            return Dtb::getTable("SELECT `nazev`, `id_instituce` FROM `instituce` ORDER BY `nazev`;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns all institution records from dtb
     * @return array
     * @throws \Exception
     */
    public function getInstitutions(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `instituce` ORDER BY `nazev` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage()); 
        }
    }
    
    /**
     * returns table of institutions
     * name and email of contact user is added to each institution
     * @return array
     * @throws \Exception
     */
    public function getInstitutionsWithCU(): array
    {
        try {
            $institutions = Dtb::getTable("SELECT * FROM `instituce` ORDER BY `nazev` ASC;");
            for ($i = 0; $i < count($institutions); $i++)
            {
                if ($institutions[$i]["id_kontaktni_uzivatel"] === null)
                {
                    $institutions[$i]["kontaktni_uzivatel"] = null;
                    $institutions[$i]["email"] = null;
                }
                else
                {
                    $user = Dtb::getOneRow("SELECT * FROM `uzivatele` WHERE `id_uzivatel` = ?;", $institutions[$i]["id_kontaktni_uzivatel"]);
                    $institutions[$i]["kontaktni_uzivatel"] = $user["jmeno"];
                    $institutions[$i]["email"] = $user["email"];
                }
            }
            return $institutions;
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage()); 
        }
    }
    
    /**
     * returns an institution record from dtb
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getInstitution(int $id): array
    {
        try {
            return Dtb::getOneRow("SELECT * FROM `instituce` WHERE `id_instituce` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage()); 
        }
    }
    
    /**
     * returns institution name from dtb
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getInstitutionName(int $id): string
    {
        try {
            return Dtb::getSingleValue("SELECT `nazev` FROM `instituce` WHERE `id_instituce` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns table of institution users
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getInstitutionUsers(int $id): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `uzivatele` WHERE `id_instituce` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns table of institution localities
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getInstitutionLocalities(int $id): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `lokality` WHERE `id_instituce` = ? ORDER BY `nazev` ASC;", $id);
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * deletes an institution from dtb
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteInstitution(int $id): void
    {
        try {
            Dtb::query("DELETE FROM `instituce` WHERE `id_instituce` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * creates and returns a validator for institution creation
     * @return FormValidator
     */
    public function getNewInstitutionValidator(): FormValidator
    {
        $validator = new FormValidator();
        $item = new FormValidatorItem("nazev", "Name");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUnique("instituce", "nazev");
        $validator->addItem($item);
        return $validator;
    }
    
    /**
     * creates and returns a validator for institution update
     * @param int $id
     * @return FormValidator
     */
    public function getEditInstitutionValidator(int $id): FormValidator
    {
        $validator = new FormValidator();
        $item = new FormValidatorItem("nazev", "Name");
        $item->addRuleIsNotEmpty()
                ->addRuleIsUniqueExcept("instituce", "nazev", "id_instituce", $id);
        $validator->addItem($item);

        $item = new FormValidatorItem("id_kontaktni_uzivatel", "Contact user");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("uzivatele", "id_uzivatel");
        $validator->addItem($item);
        
        return $validator;
    }
}

