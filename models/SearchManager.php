<?php
class SearchManager
{
    /**
     * creates and returns a validator for the search dialogue
     * @return FormValidator
     */
    public function getSearchValidator(): FormValidator
    {
        $validator = new FormValidator();
        $item = new FormValidatorItem("variable_#", "Variable");
        $item->addRuleIsKeyFromTable("veliciny", "id_velicina");
        $validator->addItem($item);

        $item = new FormValidatorItem("from", "Period from");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsNotEmpty();
        $validator->addItem($item);

        $item = new FormValidatorItem("to", "Period to");
        $item->setConversion(-1, null)
                ->addRuleCanBeNull()
                ->addRuleIsNotEmpty();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("soiltype_#", "Soil type");
        $item->addRuleIsKeyFromTable("ptypy", "id_ptyp");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("soiltexture_#", "Soil texture");
        $item->addRuleIsKeyFromTable("pdruhy", "id_pdruh");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("landcover_#", "Landcover");
        $item->addRuleIsKeyFromTable("landcovery", "id_landcover");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("slope_#", "Slope");
        $item->addRuleIsKeyFromTable("sklony", "id_sklon");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("exposition_#", "Exposition");
        $item->addRuleIsKeyFromTable("expozice", "id_expozice");
        $validator->addItem($item);
        
        $item = new FormValidatorItem("alt-from", "Altitude / From");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("alt-to", "Altitude / To");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("map-from", "Precipitation / From");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("map-to", "Precipitation / To");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("mat-from", "Temperature / From");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        $item = new FormValidatorItem("mat-to", "Temperature / To");
        $item->setConversion("", null)
                ->addRuleCanBeNull()
                ->addRuleIsFloat();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * extracts values whose keys start with given prefix
     * @param string $prefix
     * @param array $formData
     * @return array
     */
    private function extractRequiredValues(string $prefix, array $formData): array
    {
        $requiredValues = [];
        foreach ($formData as $key => $value)
        {
            if (mb_strpos($key, $prefix) !== false)
                $requiredValues[] = $value;
        }
        return $requiredValues;
    }
    
    /**
     * removes localities which does not fit monitoring criteria
     * @param array $localities
     * @param array $formData - searching criteria
     * @return void
     * @throws \Exception
     */
    private function monitoringSortOut(array & $localities, array $formData): void
    {
        $requiredVariables = $this->extractRequiredValues("variable_", $formData);
        
        $from = null;
        if ($formData["from"] !== null)
            $from = $formData["from"] . "-01";
        
        $to = null;
        if ($formData["to"] !== null)
            $to = $formData["to"] . "-01";
        
        $localityMng = new LocalityManager();
        try
        {
            $keys = array_keys($localities);
            foreach ($keys as $key)
            {
                $localityResult = true;
                foreach ($requiredVariables as $var)
                {
                    if (!$localityMng->localityMonitoresVariable($localities[$key]["id_lokalita"], $var, $from, $to))
                    {
                        $localityResult = false;
                        break;
                    }
                }
                if (!$localityResult)
                    unset($localities[$key]);
            }
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /**
     * removes localities which does not contain any of required values in given column
     * @param array $localities
     * @param string $columnName
     * @param array $requiredValues
     * @return void
     */
    private function sortOutByListOfValues(array & $localities, string $columnName, array $requiredValues): void
    {
        if (count($requiredValues) === 0)
            return;
        
        $keys = array_keys($localities);
        foreach ($keys as $key)
        {
            if ($localities[$key]["$columnName"] === null)
                continue;
            
            $localityResult = false;
            foreach ($requiredValues as $val)
            {
                if ($localities[$key]["$columnName"] == $val)
                {
                    $localityResult = true;
                    break;
                }
            }
            if (!$localityResult)
                unset($localities[$key]);
        }
    }
    
    /**
     * removes localities with values out of range in given column
     * @param array $localities
     * @param string $columnName
     * @param float|null $from
     * @param float|null $to
     * @return void
     */
    private function sortOutByRange(array & $localities, string $columnName, ?float $from, ?float $to): void
    {
        $keys = array_keys($localities);
        foreach ($keys as $key)
        {
            $locVal = $localities[$key]["$columnName"];
            
            if ($locVal === null)
                continue;
            
            if ($from !== null && $locVal < $from)
            {
                unset($localities[$key]);
                continue;
            }
            
            if ($to !== null && $locVal > $to)
            {
                unset($localities[$key]);
                continue;
            }
        }
    }
    
    /**
     * removes localities which does not fit geography criteria
     * @param array $localities
     * @param array $formData
     * @return void
     */
    private function geographySortOut(array & $localities, array $formData): void
    {
        $requiredSoiltypes = $this->extractRequiredValues("soiltype_", $formData);
        $this->sortOutByListOfValues($localities, "pudni_typ", $requiredSoiltypes);
        
        $requiredSoiltextures = $this->extractRequiredValues("soiltexture_", $formData);
        $this->sortOutByListOfValues($localities, "pudni_druh", $requiredSoiltextures);
        
        $requiredSlopes = $this->extractRequiredValues("slope_", $formData);
        $this->sortOutByListOfValues($localities, "sklon", $requiredSlopes);
        
        $requiredExpositions = $this->extractRequiredValues("exposition_", $formData);
        $this->sortOutByListOfValues($localities, "expozice", $requiredExpositions);
        
        $requiredLandcovers = $this->extractRequiredValues("landcover_", $formData);
        $this->sortOutByListOfValues($localities, "landcover", $requiredLandcovers);
        
        $this->sortOutByRange($localities, "nadm_vyska", $formData["alt-from"],$formData["alt-to"]);
        
        $this->sortOutByRange($localities, "rocni_srazky", $formData["map-from"],$formData["map-to"]);
        
        $this->sortOutByRange($localities, "rocni_teplota", $formData["mat-from"],$formData["mat-to"]);
    }
    
    /**
     * final search method
     * returns table of localities satisfying criteria
     * @param array $formData
     * @return array
     * @throws \Exception
     */
    public function search(array $formData): array
    {
        // complete localities list
        $localityMng = new LocalityManager();
        try { $localities = $localityMng->getLocalities(); }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
        
        // monitoring sort out
        try { $this->monitoringSortOut($localities, $formData); }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
        
        // geography sort out
        $this->geographySortOut($localities, $formData);
        
        // return remaining localities
        return $localities;
    }
}




























