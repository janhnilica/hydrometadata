<?php
class DownloadController extends Controller
{
    public function process(array $params): void
    {
        $downloadMng = new DownloadManager();
        $localityMng = new LocalityManager();
        $institutionMng = new InstitutionManager();
        
        $data = "Hydrometadata search results\n";
        
        try
        {
            $dt = new \DateTime();
            $data .= $dt->format("j. n. Y G:i") . "\n\n";
            
            foreach ($params as $id)
            {
                if (!ctype_digit($id))
                    $this->redirect(ERR_NOTFOUND);
                
                $locality = $localityMng->getLocality($id);
                if (empty($locality))
                    $this->redirect(ERR_NOTFOUND);
                
                $institution = $institutionMng->getInstitution($locality["id_instituce"]);
                if (empty($institution))
                    $this->redirect(ERR_NOTFOUND);
                
                $data .= "Locality: " . $locality["nazev"] . "\n";
                $data .= "Institution: " . $institution["nazev"] . "\n\n";
            }
        }
        catch (\Exception $ex)
        {
            logException($ex);
            $this->redirect(ERR_SERVER_ERROR);
        }
        
        $downloadMng->downloadTxt($data, "search-results.txt");
    }
}
