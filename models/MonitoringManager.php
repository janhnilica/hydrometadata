<?php
class MonitoringManager
{
    /**
     * returns dtb date formatted for presentation
     * @param string $date
     * @return string
     */
    public function formatDate(?string $date): string
    {
        if ($date === null)
            return "not defined";
        if ($date === "9999-12-01")
            return "still monitoring";
        $dt = new DateTime($date);
        return $dt->format("m / Y");
    }
    
    /**
     * returns a table of variables monitored at locality
     * the key "variable" containing variable name is added to each variable
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getLocalityMonitoring(int $id): array
    {
        try {
           $monitoring = Dtb::getTable("SELECT * FROM `mereni` WHERE `id_lokalita` = ? ORDER BY `id_mereni` DESC;", $id);
           for ($i = 0; $i < count($monitoring); $i++)
           {
               $monitoring[$i]["variable"] = Dtb::getSingleValue("SELECT `nazev` FROM `veliciny` WHERE `id_velicina` = ?;", $monitoring[$i]["id_velicina"]);
               $monitoring[$i]["start"] = $this->formatDate($monitoring[$i]["start"]);
               $monitoring[$i]["konec"] = $this->formatDate($monitoring[$i]["konec"]);
           }
           return $monitoring;
        }
        catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns an array to fill a from / to select
     * @param bool $stillMonitoringOption - whether to add a item "9999-12" => "still monitoring"
     * @return array
     */
    public function getTimeSelectTable(bool $stillMonitoringOption): array
    {
        $table = [];
        $table[] = ["value" => "-1", "label" => "not defined"];
        if ($stillMonitoringOption)
            $table[] = ["value" => "9999-12", "label" => "still monitoring"];
        
        $currentYear = date("Y");
        $currentMonth = date("m");
        
        for ($month = $currentMonth; $month > 0; $month--)
        {
            if ($month < 10)
                $month = "0" . $month;
            $table[] = ["value" => "$currentYear-$month", "label" => "$month / $currentYear"];
        }
        
        for ($year = $currentYear - 1; $year >= 1900; $year--)
        {
            for ($month = 12; $month > 0; $month--)
            {
                if ($month < 10)
                    $month = "0" . $month;
                $table[] = ["value" => "$year-$month", "label" => "$month / $year"];
            }
        }
        
        return $table;
    }
    
    /**
     * creates and returns a validator for monitoring creation
     * @return FormValidator
     */
    public function getMonitoringValidator(): FormValidator
    {
        $validator = new FormValidator();
        $item = new FormValidatorItem("id_velicina", "Variable");
        $item->addRuleIsNotEmpty()
                ->addRuleIsKeyFromTable("veliciny", "id_velicina");
        $validator->addItem($item);

        $item = new FormValidatorItem("start", "From");
        $item->setConversion("-1", null)
                ->addRuleCanBeNull()
                ->addRuleIsNotEmpty();
        $validator->addItem($item);

        $item = new FormValidatorItem("konec", "To");
        $item->setConversion("-1", null)
                ->addRuleCanBeNull()
                ->addRuleIsNotEmpty();
        $validator->addItem($item);

        $item = new FormValidatorItem("poznamky", "Comment");
        $item->setConversion("", null)
                ->addRuleCanBeNull();
        $validator->addItem($item);
        
        return $validator;
    }
    
    /**
     * saves a single monitoring record to dtb
     * @param array $formData - validated
     * @param int $idLocality
     * @return void
     * @throws \Exception
     */
    public function saveMonitoring(array $formData, int $idLocality): void
    {
        $formData["id_lokalita"] = $idLocality;
        if ($formData["start"] !== null)
            $formData["start"] .= "-01";
        if ($formData["konec"] !== null)
            $formData["konec"] .= "-01";
        try {
            Dtb::insert("mereni", $formData);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * updates a single monitoring record
     * @param array $formData
     * @param int $idMonitoring
     * @return void
     * @throws \Exception
     */
    public function updateMonitoring(array $formData, int $idMonitoring): void
    {
        if ($formData["start"] !== null)
            $formData["start"] .= "-01";
        if ($formData["konec"] !== null)
            $formData["konec"] .= "-01";
        try {
            Dtb::update("mereni", $formData, "WHERE `id_mereni` = ?", [$idMonitoring]);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * deletes a monitoring record from dtb
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteMonitoring(int $id): void
    {
        try {
            Dtb::query("DELETE FROM `mereni` WHERE `id_mereni` = ?", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * trims day values from "start" and "konec" items
     * @param array $monitoringRecord
     * @return void
     */
    public function trimMonitoringDates(array & $monitoringRecord): void
    {
        if ($monitoringRecord["start"] != null)
            $monitoringRecord["start"] = substr($monitoringRecord["start"], 0, 7);
        if ($monitoringRecord["konec"] != null)
            $monitoringRecord["konec"] = substr($monitoringRecord["konec"], 0, 7);
    }
    
    /**
     * returns a single monitoring record
     * @param type $id
     * @return array
     * @throws \Exception
     */    
    public function getSingleMonitoring($id): array
    {
        try {
            return Dtb::getOneRow("SELECT * FROM `mereni` WHERE `id_mereni` = ?;", $id);
        } catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}



































