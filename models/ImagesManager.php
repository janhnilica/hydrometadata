<?php
class ImagesManager
{
    /**
     * ensures the existence of the main images directory
     * if it does not exist, creates it
     * @return void
     * @throws \Exception
     */
    private function ensureMainImagesDirectory(): void
    {
        try
        {
            if (!is_dir(LOCALITY_IMAGES_DIR))
                if (!mkdir(LOCALITY_IMAGES_DIR))
                    throw new \Exception("Main images directory creation failed.");
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /**
     * finds whether a locality has its directory for images
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    private function localityHasImagesDirectory(int $id): bool
    {
        try
        {
            $pathToDir = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id;
            return (is_dir($pathToDir));
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /**
     * returns a number of images of given locality
     * @param int $id
     * @return int
     * @throws \Exception
     */
    private function getNumberOfLocalityImages(int $id): int
    {
        try
        {
            $pathToDir = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id;
            if (!is_dir($pathToDir))
                return 0;
            $items = scandir($pathToDir);
            if ($items === false)
                throw new \Exception("Scanning of locality images directory failed.");
            $n = 0;
            foreach ($items as $item)
            {
                if ($item != "." && $item != ".." && $item != "min")
                    $n += 1;
            }
            return $n;
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }

    /**
     * returns a list of locality images
     * returns an array of 2 arrays: ["names", "suffixes"]
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getListOfLocalityImages(int $id): array
    {
        try
        {
            $pathToDir = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id;
            if (!is_dir($pathToDir))
                return array("names" => array(), "suffixes" => array());
            $items = scandir($pathToDir);
            if ($items === false)
                throw new \Exception("Scanning of locality images directory failed.");
            $names = array();
            $suff = array();
            foreach ($items as $item)
            {
                if ($item != "." && $item != ".." && $item != "min")
                {
                    $parts = explode(".", $item);
                    $names[] = $parts[0];
                    $suff[] = $parts[1];
                }
            }
            return ["names" => $names, "suffixes" =>$suff];
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }

    
    /**
     * searches $_FILES and returns an array of Image objects
     * throws:
     *  a) a UserException with a corresponding message in case of:
     *      - incorrect form input name
     *      - no file uploaded
     *      - incorrect file type
     *      - upload fail
     *  b) an \Exception for all other problems
     * @param string $inputName
     * @return array
     * @throws UserException
     * @throws \Exception
     */
    public function getUploadedImages(string $inputName): array
    {
        try
        {
            // wrong input name
            if (!isset($_FILES[$inputName]))
                throw new UserException("Wrong form input name for images!");
            
            // no file uploaded
            if ($_FILES[$inputName]["error"][0] == UPLOAD_ERR_NO_FILE)
                throw new UserException("No file uploaded.");

            // iteration through the uploaded files
            $images = array();
            $n = count($_FILES[$inputName]["error"]);
            for ($i = 0; $i < $n; $i++)
            {
                if ($_FILES[$inputName]["error"][$i] != UPLOAD_ERR_OK)
                    throw new UserException("Upload failed.");
                
                if (!Image::isImage($_FILES[$inputName]["tmp_name"][$i]))
                    throw new UserException("Any of upload files is not an image.");
                
                $images[] = new Image($_FILES[$inputName]["tmp_name"][$i]);
            }
            return $images;
        }
        catch (UserException $ex) { throw new UserException($ex->getMessage()); }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /////////////////////////
    // OPRAVIT, DELA CHYBY //
    /////////////////////////
    /**
     * saves images and miniatures to locality directory
     * if locality has no images yet, creates the directory
     * @param int $id
     * @param array $images - array of Image objects
     * @return void
     * @throws \Exception
     */
    public function saveLocalityImages(int $id, array $images): void
    {
        try
        {
            $this->ensureMainImagesDirectory();
            $pathToDir = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $id;
            
            if ($this->localityHasImagesDirectory($id))
            {
                $currentImages = $this->getListOfLocalityImages($id);
                if (count($currentImages["names"]) === 0)
                    $startNumber = 0;
                else
                    $startNumber = max($currentImages["names"]) + 1;
            }
            else
            {
                if (!mkdir($pathToDir))
                    throw new \Exception("Creation of locality images directory failed.");
                if (!mkdir($pathToDir . DIRECTORY_SEPARATOR . "min"))
                    throw new \Exception("Creation of locality images directory failed.");
                $startNumber = 0;
            }
            
            // large images
            $n = count($images);
            for ($i = 0; $i < $n; $i++)
            {
                $images[$i]->resizeToEdge(intval(BIG_IMAGE_EDGE));
                $images[$i]->save($pathToDir . DIRECTORY_SEPARATOR . ($startNumber + $i) . ".jpg");
            }

            // miniatures
            for ($i = 0; $i < $n; $i++)
            {
                $images[$i]->resizeToEdge(intval(SMALL_IMAGE_EDGE));
                $images[$i]->save($pathToDir . DIRECTORY_SEPARATOR . "min" . DIRECTORY_SEPARATOR . ($startNumber + $i) . ".jpg");
            }
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    /**
     * deletes a single image from locality images directory
     * if the directory is empty after the deletion, function also deletes the directory
     * @param int $localityId
     * @param string $imageName
     * @return void
     * @throws \Exception
     */
    public function deleteImage(int $localityId, string $imageName): void
    {
        try
        {
            $pathToDir = LOCALITY_IMAGES_DIR . DIRECTORY_SEPARATOR . $localityId;
            $pathToImage =  $pathToDir . DIRECTORY_SEPARATOR . $imageName . ".jpg";
            $pathToMiniature = $pathToDir . DIRECTORY_SEPARATOR . "min" . DIRECTORY_SEPARATOR . $imageName . ".jpg";
            if (!file_exists($pathToImage) || !file_exists($pathToMiniature))
                throw new \Exception("Cannot delete locality image - not found.");
            
            if (!unlink($pathToImage) || !unlink($pathToMiniature))
                throw new \Exception("Image deletion failed.");
            
            $remains = $this->getNumberOfLocalityImages($localityId);
            if ($remains === 0)
            {
                if (!rmdir($pathToDir . DIRECTORY_SEPARATOR . "min"))
                    throw new \Exception("Image directory deletion failed.");
                if (!rmdir($pathToDir))
                    throw new \Exception("Image directory deletion failed.");
            }
        }
        catch (\Exception $ex) { throw new \Exception($ex->getMessage()); }
    }
    
    
    /**
     * deletes all locality images and locality images directory
     * @param int $id
     * @return void
     * @throws UserException
     */
    public function deleteLocalityImages(int $id): void
    {
        try
        {
            $images = $this->getListOfLocalityImages($id);
            foreach ($images["names"] as $img)
                $this->deleteImage($id, $img);
        } 
        catch (\Exception $ex) { throw new UserException("Locality $id: " . $ex->getMessage()); }
    }
}
