<?php
class TableManager
{
    /**
     * returns complete data for table monitoring overview
     * @return array
     * @throws \Exception
     */
    public function getTableData(): array
    {
        $institutionMng = new InstitutionManager();
        $localityMng = new LocalityManager();
        
        try
        {
            $institutions = $institutionMng->getInstitutions();
            
            $localities = [];
            foreach ($institutions as $inst)
                $localities[$inst["id_instituce"]] = $institutionMng->getInstitutionLocalities($inst["id_instituce"]);
            
            $monitoring = [];
            foreach ($localities as $locList)
            {
                foreach ($locList as $loc)
                    $monitoring[$loc["id_lokalita"]] = $localityMng->getLocalityMonitoredVariables($loc["id_lokalita"]);
            }
            
            return ["institutions" => $institutions, "localities" => $localities, "monitoring" => $monitoring];
        }
        catch (PDOException $ex)
        {
            throw new \Exception($ex->getMessage());
        }
    }
}
