<?php
class TableController extends Controller
{
    public function process(array $params): void
    {
        $tableMng = new TableManager();
        $institutionMng = new InstitutionManager();
        $landcoverMng = new LandcoverManager();
        $variablesMng = new VariablesManager();
        
        // monitoring
        if (count($params) === 1 && $params[0] === "monitoring")
        {
            try { $tableData = $tableMng->getTableData(); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->data["institutions"] = $tableData["institutions"];
            $this->data["localities"] = $tableData["localities"];
            $this->data["monitoring"] = $tableData["monitoring"];
            $this->header["description"] = "overview of institutions, measuring sites and variables";
            $this->header["keywords"] = "map, metadata, monitoring, measurement, environment";
            $this->header["title"] = $this->createTitle("Monitoring overview");
            $this->view = "table-monitoring";
        }
        
        // institutions
        elseif (count($params) === 1 && $params[0] === "institutions")
        {
            try { $institutions = $institutionMng->getInstitutionsWithCU(); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->data["institutions"] = $institutions;
            $this->header["description"] = "overview of institutions";
            $this->header["keywords"] = "map, metadata, monitoring, measurement, environment";
            $this->header["title"] = $this->createTitle("Institutions overview");
            $this->view = "table-institutions";
        }
        
        // landcovers
        elseif (count($params) === 1 && $params[0] === "landcovers")
        {
            try { $landcovers = $landcoverMng->getLandcovers(); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->data["landcovers"] = $landcovers;
            $this->header["description"] = "overview of landcovers";
            $this->header["keywords"] = "map, metadata, monitoring, measurement, environment";
            $this->header["title"] = $this->createTitle("Landcovers overview");
            $this->view = "table-landcovers";
        }
        
        // variables
        elseif (count($params) === 1 && $params[0] === "variables")
        {
            try { $variables = $variablesMng->getVariables(); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $this->data["variables"] = $variables;
            $this->header["description"] = "overview of variables";
            $this->header["keywords"] = "map, metadata, monitoring, measurement, environment";
            $this->header["title"] = $this->createTitle("Variables overview");
            $this->view = "table-variables";
        }
        
        else
            $this->redirect(ERR_NOTFOUND);
    
    }
}
