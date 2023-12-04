<?php
class MapManager
{
    /**
     * returns array of separated arrays of localities data for individual institutions
     * @param array $institutions - institutions data from dtb
     * @return array
     * @throws \Exception
     */
    public function getMarkersData(array $institutions): array
    {
        $markersData = [];
        try
        {
            foreach ($institutions as $inst)
            {
                $data = Dtb::getTable("SELECT `id_lokalita`, `nazev`, `zem_delka`, `zem_sirka` FROM `lokality` WHERE `id_instituce` = ? ORDER BY `nazev` ASC;", $inst["id_instituce"]);
                $markersData[] = $data;
            }
            return $markersData;
        }
        catch (PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
    
    /**
     * returns a new locality id from SESSION (or null)
     * @return mixed
     */
    public function getNewLocalityId(): mixed
    {
        $newLocalityId = "null";
        if (isset($_SESSION["newLocalityId"]))
        {
            $newLocalityId = intval($_SESSION["newLocalityId"]);
            unset($_SESSION["newLocalityId"]);
        }
        return $newLocalityId;
    }
    
    /**
     * returns an array of search results from SESSION
     * @return array
     */
    public function getSearchResults(): array
    {
        $results = [];
        if (isset($_SESSION["search-results"]))
        {
            $results = $_SESSION["search-results"];
            unset($_SESSION["search-results"]);
        }
        return $results;
    }
}

