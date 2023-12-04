<?php
class MonitoringController extends Controller
{
    public function process(array $params): void
    {
        $variablesMng = new VariablesManager();
        $localityMng = new LocalityManager();
        $monitoringMng = new MonitoringManager();
        $userMng = new UserManager();
        $user = $userMng->getLoggedUser();
        
        if (!$user)
            $this->redirect(ERR_UNAUTHORIZED);

        if (count($params) === 0 || !ctype_digit($params[0]))
            $this->redirect(ERR_NOTFOUND);
        
        $locId = intval($params[0]);
        try
        {
            $locality = $localityMng->getLocality($locId);
            $monitorning = $monitoringMng->getLocalityMonitoring($locId);
            $variables = $variablesMng->getVariables();
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }

        if (empty($locality))
            $this->redirect(ERR_NOTFOUND);
        if ($locality["id_instituce"] != $user["id_instituce"])
            $this->redirect(ERR_FORBIDDEN);
        
        $validator = $monitoringMng->getMonitoringValidator();
        
        // main monitoring page
        if (count($params) === 1)
        {
            $formData = $this->getInitialFormData();
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("monitoring/$locId", $formData, $validator);
                
                try { $monitoringMng->saveMonitoring($formData, $locId); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Addition of monitoring to " . $locality["nazev"]);
                $this->addMessage("Monitored variable has been added", "info-message");
                $this->redirect("monitoring/$locId");
            }
            
            $this->data["formData"] = $formData;
            $this->data["locality"] = $locality;
            $this->data["monitoring"] = $monitorning;
            $this->data["fromTable"] = $monitoringMng->getTimeSelectTable(false);
            $this->data["toTable"] = $monitoringMng->getTimeSelectTable(true);
            $this->data["variables"] = $variables;
            $this->header["title"] = $this->createTitle("Locality monitoring");
            $this->view = "monitoring-main";
        }
        
        // edit single monitoring
        elseif (count($params) === 2 && ctype_digit($params[1]))
        {
            $monId = intval($params[1]);
            
            try { $monRecord = $monitoringMng->getSingleMonitoring($monId); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if (empty($monRecord))
                $this->redirect(ERR_NOTFOUND);
            
            if ($monRecord["id_lokalita"] != $locality["id_lokalita"])
                $this->redirect(ERR_FORBIDDEN);
            
            try
            {
                $formData = $this->getInitialFormData("mereni", "id_mereni", $monId);
                $monitoringMng->trimMonitoringDates($formData);
            }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if ($_POST)
            {
                $formData = $validator->extractFormData();
                $this->performValidation("monitoring/$locId/$monId", $formData, $validator);
                
                try { $monitoringMng->updateMonitoring($formData, $monId); }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
                
                $userMng->saveUserActivity("Update of monitoring in " . $locality["nazev"]);
                $this->addMessage("Monitored variable has been updated", "info-message");
                $this->redirect("monitoring/$locId");
            }
            
            $this->data["formData"] = $formData;
            $this->data["locality"] = $locality;
            $this->data["fromTable"] = $monitoringMng->getTimeSelectTable(false);
            $this->data["toTable"] = $monitoringMng->getTimeSelectTable(true);
            $this->data["variables"] = $variables;
            $this->header["title"] = $this->createTitle("Monitoring editation");
            $this->view = "monitoring-edit";
        }
        
        // delete single monitoring
        elseif (count($params) === 3 && ctype_digit($params[1]) && $params[2] === "delete")
        {
            $monId = intval($params[1]);
            
            try { $monRecord = $monitoringMng->getSingleMonitoring($monId); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if (empty($monRecord))
                $this->redirect(ERR_NOTFOUND);
            
            if ($monRecord["id_lokalita"] != $locality["id_lokalita"])
                $this->redirect(ERR_FORBIDDEN);
            
            try { $monitoringMng->deleteMonitoring($monId); }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            $userMng->saveUserActivity("Deletion of monitoring in " . $locality["nazev"]);
            $this->addMessage("Monitored variable has been deleted", "info-message");
            $this->redirect("monitoring/$locId");
        }
        
        // incorrect url
        else
            $this->redirect(ERR_NOTFOUND);
    }
}
