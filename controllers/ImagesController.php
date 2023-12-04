<?php
class ImagesController extends Controller
{
    public function process(array $params): void
    {
        $userMng = new UserManager();
        $localityMng = new LocalityManager();
        $imagesMng = new ImagesManager();
                
        $user = $userMng->getLoggedUser();
        if (!$user)
            $this->redirect(ERR_UNAUTHORIZED);
        
        // locality overview
        if (count($params) === 1 && ctype_digit($params[0]))
        {
            $id = intval($params[0]);
            $locality = $localityMng->getLocality($id);
            
            if (empty($locality))
                $this->redirect(ERR_NOTFOUND);
            
            if ($locality["id_instituce"] != $user["id_instituce"])
                $this->redirect(ERR_FORBIDDEN);
            
            try { $currentImages = $imagesMng->getListOfLocalityImages($id); }
            catch (\Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
            
            if ($_FILES)
            {
                try
                {
                    $images = $imagesMng->getUploadedImages("images");
                    $imagesMng->saveLocalityImages($id, $images);
                    $userMng->saveUserActivity("Addition of images to " . $locality["nazev"]);
                    $this->addMessage("Images have been added");
                    $this->redirect("images/$id");
                }
                catch (UserException $ex)
                {
                    $this->addMessage($ex->getMessage(), "warning-message");
                    $this->redirect("images/$id");
                }
                catch (\Exception $ex)
                {
                    logException($ex);
                    $this->redirect(ERR_SERVER_ERROR);
                }
            }
            
            $this->data["pathToDir"] = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
            $this->data["currentImages"] = $currentImages["names"];
            $this->data["locality"] = $locality;
            $this->header["title"] = $this->createTitle("Locality images");
            $this->view = "images";
        }
        
        // delete single image
        elseif (count($params) === 2 && ctype_digit($params[0]) && ctype_digit($params[1]))
        {
            $localityId = intval($params[0]);
            $imageName = $params[1];
            
            $locality = $localityMng->getLocality($localityId);
            
            if (empty($locality))
                $this->redirect(ERR_NOTFOUND);
            
            if ($locality["id_instituce"] != $user["id_instituce"])
                $this->redirect(ERR_FORBIDDEN);
            
            try
            {
                $imagesMng->deleteImage($localityId, $imageName);
                $userMng->saveUserActivity("Deletion of images in " . $locality["nazev"]);
                $this->addMessage("Image has been deleted");
                $this->redirect("images/$localityId");
            }
            catch (Exception $ex)
            {
                logException($ex);
                $this->redirect(ERR_SERVER_ERROR);
            }
        }
        
        // incorrect url
        else
            $this-$this->redirect(ERR_NOTFOUND);
    }
}
