<?php
class DownloadManager
{
    private function obCleanAll()
    {
        $ob_active = ob_get_length () !== false;
        while($ob_active)
        {
            ob_end_clean();
            $ob_active = ob_get_length () !== false;
        }

        return true;
    }
    
    public function downloadTxt(string $data, string $fileName)
    {
        $filesize = mb_strlen($data);
        $this->obCleanAll();

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: txt");
        header("Content-Length: " . $filesize);
        header("Content-Disposition: attachment; filename=" . $fileName . ";" );

        echo $data;
        die();
    }
}
