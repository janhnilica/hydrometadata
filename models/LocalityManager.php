<?php
class LocalityManager
{
    /**
     * returns complete table of localities
     * @return array
     * @throws \Exception
     */
    public function getLocalities(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `lokality` ORDER BY `nazev` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns a locality record from dtb
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getLocality(int $id): array
    {
        try {
            return Dtb::getOneRow("SELECT * FROM `lokality` WHERE `id_lokalita` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns an array of locality data in a form for direct presentation
     * identifiers from other tables are replaced by item names (or by "not defined")
     * an item "instituce" containing institution name is added into array
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getLocalityPresentation(int $id): array
    {
        try
        {
            $loc = Dtb::getOneRow("SELECT * FROM `lokality` WHERE `id_lokalita` = ?;", $id);
            if (empty($loc))
                return [];
            
            $loc["instituce"] = Dtb::getSingleValue("SELECT `nazev` FROM `instituce` WHERE `id_instituce` = ?;", $loc["id_instituce"]);
            
            if ($loc["nadm_vyska"] === null)
                $loc["nadm_vyska"] = "not defined";
            else
                $loc["nadm_vyska"] .= " m.a.s.l.";
            
            if ($loc["sklon"] === null)
                $loc["sklon"] = "not defined";
            else
                $loc["sklon"] = DTb::getSingleValue ("SELECT `nazev` FROM `sklony` WHERE `id_sklon` = ?;", $loc["sklon"]);
            
            if ($loc["expozice"] === null)
                $loc["expozice"] = "not defined";
            else
                $loc["expozice"] = DTb::getSingleValue ("SELECT `nazev` FROM `expozice` WHERE `id_expozice` = ?;", $loc["expozice"]);
            
            if ($loc["pudni_typ"] === null)
                $loc["pudni_typ"] = "not defined";
            else
                $loc["pudni_typ"] = DTb::getSingleValue ("SELECT `nazev` FROM `ptypy` WHERE `id_ptyp` = ?;", $loc["pudni_typ"]);
            
            if ($loc["pudni_druh"] === null)
                $loc["pudni_druh"] = "not defined";
            else
                $loc["pudni_druh"] = DTb::getSingleValue ("SELECT `nazev` FROM `pdruhy` WHERE `id_pdruh` = ?;", $loc["pudni_druh"]);
            
            if ($loc["horizonty"] === null)
                $loc["horizonty"] = "not defined";
            
            if ($loc["rocni_srazky"] === null)
                $loc["rocni_srazky"] = "not defined";
            else
                $loc["rocni_srazky"] .= " mm";
            
            if ($loc["rocni_teplota"] === null)
                $loc["rocni_teplota"] = "not defined";
            else
                $loc["rocni_teplota"] .= " °C";
            
            if ($loc["landcover"] === null)
                $loc["landcover"] = "not defined";
            else
                $loc["landcover"] = DTb::getSingleValue ("SELECT `nazev` FROM `landcovery` WHERE `id_landcover` = ?;", $loc["landcover"]);
            
            return $loc;
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /**
     * deletes locality record from dtb
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteLocality(int $id): void
    {
        try {
            Dtb::query("DELETE FROM `lokality` WHERE `id_lokalita` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns all slopes from dtb
     * @return array
     * @throws \Exception
     */
    public function getSlopes(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `sklony` ORDER BY `id_sklon` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns all expositions from dtb
     * @return array
     * @throws \Exception
     */
    public function getExpositions(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `expozice` ORDER BY `id_expozice` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns all soil types from dtb
     * @return array
     * @throws \Exception
     */
    public function getSoilTypes(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `ptypy` ORDER BY `id_ptyp` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns all soil textures from dtb
     * @return array
     * @throws \Exception
     */
    public function getSoilTextures(): array
    {
        try {
            return Dtb::getTable("SELECT * FROM `pdruhy` ORDER BY `id_pdruh` ASC;");
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    
    /**
     * creates and returns a validator for locality creation / update
     * @return FormValidator
     */
    public function getLocalityValidator(): FormValidator
    {
        $validator = new FormValidator();
        
        $item = new FormValidatorItem("nazev", "Name");
        $item->addRuleIsNotEmpty();
        $validator->addItem($item);

        $item = new FormValidatorItem("zem_delka", "Longitude");
        $item->addRuleIsFloat();
        $validator->addItem($item);

        $item = new FormValidatorItem("zem_sirka", "Latitude");
        $item->addRuleIsFloat();
        $validator->addItem($item);

        $item = new FormValidatorItem("nadm_vyska", "Elevation");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("sklon", "Slope");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("sklony", "id_sklon");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("expozice", "Exposition");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("expozice", "id_expozice");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("pudni_typ", "Soil type");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("ptypy", "id_ptyp");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("pudni_druh", "Soil texture");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("pdruhy", "id_pdruh");
        $validator->addItem($item);

        $item = new FormValidatorItem("horizonty", "Soil horizons");
        $item->setConversion("", null)
                ->addRuleCanBeNull();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("rocni_srazky", "Mean annual precipitation");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);

        $item = new FormValidatorItem("rocni_teplota", "Mean annual temperature");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);

        $item = new FormValidatorItem("landcover", "Landcover");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsKeyFromTable("landcovery", "id_landcover");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("poznamky", "Comment");
        $item->setConversion("", null)
                ->addRuleCanBeNull();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * returns table of names of variables measured on given locality
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getLocalityMonitoredVariables(int $id): array
    {
        try {
            $table =  Dtb::getTable("SELECT `veliciny`.`nazev` FROM `veliciny`"
                    . " INNER JOIN `mereni` ON `mereni`.`id_velicina` = `veliciny`.`id_velicina`"
                    . " INNER JOIN `lokality` ON `lokality`.`id_lokalita` = `mereni`.`id_lokalita`"
                    . " WHERE `lokality`.`id_lokalita` = ?"
                    . " ORDER BY `veliciny`.`nazev` ASC;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
        
        $names = [];
        foreach ($table as $row)
            $names[] = $row["nazev"];
        return $names;
    }
    
    /**
     * tests whether a given variable is monitored on locality in given period
     * @param int $idLocality
     * @param int $idVariable
     * @param string|null $from
     * @param string|null $to
     * @return bool
     * @throws \Exception
     */
    public function localityMonitoresVariable(int $idLocality, int $idVariable, ?string $from = null, ?string $to = null): bool
    {
        try { $monitoring = Dtb::getTable("SELECT * FROM `mereni` WHERE `id_lokalita` = ? AND `id_velicina` = ?;", $idLocality, $idVariable); }
        catch (Exception $ex) { throw new \Exception($ex->getMessage()); }
        
        if (empty($monitoring))
            return false;
        
        $fromRequired = null;
        if ($from !== null)
            $fromRequired = new \DateTime($from);
        
        $toRequired = null;
        if ($to !== null)
            $toRequired = new \DateTime($to);
        
        foreach ($monitoring as $mnt)
        {
            $singleResult = true;
            
            if ($fromRequired !== null && $mnt["start"] !== null)
            {
                $fromReal = new \DateTime($mnt["start"]);
                if ($fromRequired < $fromReal)
                    $singleResult = false;
            }
            
            if ($toRequired !== null && $mnt["konec"] !== null)
            {
                $toReal = new \DateTime($mnt["konec"]);
                if ($toRequired > $toReal)
                    $singleResult = false;
            }
            
            if ($singleResult)
                return true;
        }
        
        return false;
    }
}




