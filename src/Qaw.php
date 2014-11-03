<?php
namespace src;

use \Endroid\QrCode\QrCode as qrCode;
use \src\WatImage as WatImage;

class Qaw {
    
    /**
     *
     * @var object
     */
    public $qrCode;
    
    /**
     *
     * @var array
     */
    public $files = array();
    
    /**
     *
     * @var string
     */
    public $saveDirectory;
    
    /**
     *
     * @var string
     */
    public $rightHardDrive;
    
    /**
     *
     * @var string
     */
    public $rightPath; 
    
    /**
     *
     * @var string
     */
    public $picturesFolder; 
    
    /**
     *
     * @var string
     */
    public $doneFolder;
    
    /**
     *
     * @var string
     */
    public $treatedFolder;    
    
    /**
     *
     * @var string
     */
    public $recoveryFolder;    
    
    /**
     *
     * @var string
     */
    public $outputFileExtension; 
    
    /**
     *
     * @var object
     */
    public $watImage;  
    
    
    /**
     *
     * @var string
     */
    public $qrCodeWidthRatio;
    
    /**
     * 
     */
    function __construct() 
    {
        $this->qrCode = new qrCode();
        $this->watImage = new WatImage();
    }
    
    /**
     * Choose the correct drive path to target
     */
    function getHardDrive()
    {    
        $drive = (string) substr(__DIR__, 0,1);

        if ($this->rightHardDrive !== $drive) {
            $this->rightPath = (string) $this->rightHardDrive . ':/' . $this->picturesFolder;
        }
    }
    
    /**
     * Set pictures folder
     * 
     * @param  string $folder Folder to use
     * @return string
     */
    function setPicturesFolder($folder)
    {
        return (string) $this->picturesFolder = (string) $this->rightPath . $folder;
    }

    /**
     * Set done folder
     * 
     * @param  string $folder Folder to use
     * @return string
     */
    function setDoneFolder($folder)
    {
        return (string) $this->doneFolder = (string) $this->rightPath . $folder;
    }
    
    /**
     * Set recovery folder
     * 
     * @param  string $folder Folder to use
     * @return string
     */
    function setRecoveryFolder($folder)
    {
        return (string) $this->recoveryFolder = (string) $this->rightPath . $folder;
    }
    
    /**
     * Set treated folder
     * 
     * @param  string $folder Folder to use
     * @return string
     */
    function setTreatedFolder($folder)
    {
        return (string) $this->treatedFolder = (string) $this->rightPath . $folder;
    }    

    /**
     * Set QrCode temporary path
     * 
     * @return string
     */
    function createNewFolder($folder)
    {
        $fullPath = $this->rightPath . $folder;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
    }    
    
    /**
     * Set qrcode size depending on image
     * 
     * @param  string $picture Picture path
     * @return integer
     */
    private function setQrCodeSize($picture) 
    {
        $size       = getimagesize($picture);
        $fileRatio  = ($size[0] > $size[1]) ? $size[0]/$size[1] : $size[1]/$size[0];
        return (integer) ceil((($size[0] * $this->qrCodeWidthRatio)/100)/$fileRatio);
    }
    
    
    /**
     * Create qrcode
     * 
     * @param string $url     Url to write in the qrcode
     * @param string $padding qrcode padding
     * 
     * @return boolean 
     */
    public function createQrCode($url, $padding)
    {
        if (!empty($this->files)) {
            foreach ($this->files as $key => $val) {
               
               $currentImageFilename   =   $this->picturesFolder . '/' . $val['picture'];

               $qrCodeSize = $this->setQrCodeSize($currentImageFilename);
               $qrCodeText = $url . $val['id'];
               $this->qrCode->setText($qrCodeText);
               $this->qrCode->setSize($qrCodeSize);
               $this->qrCode->setPadding($padding);
               if (!$this->qrCode->save($this->picturesFolder . '/' . $val['qrcode'])) {
                   return false;
               }              
            }
        }
        
        return true;
    }
    
    /**
     * Create the ini file
     * 
     * @return boolean
     */
    public function createIniFile()
    {
        if (!empty($this->files)) {
            foreach ($this->files as $key => $val) {
               
                $iniFile                =   $this->picturesFolder . '/' . $val['id'] . '.ini';
                
                $extension              = str_replace('.', '', $this->outputFileExtension);
                
                //Save producer infos
                $datas  = "uid = '".$val['id']."'" . "\r\n";
                $datas .= "filename = '".$val['id']."'" . "\r\n";
                $datas .= "extension = '".$extension."'" . "\r\n";

                $handle = fopen($iniFile, 'w+');
                if (fwrite($handle, $datas)) {
                   fclose($handle);
                }            
            }
        }
        return true;
    }    
    
    /**
     * Save original files to
     * 
     * @param  string  $folder Folder where to save files
     * 
     * @return boolean
     */
    public function saveCurrentFilesTo($folder) 
    {
        $result = true;
        
        $newFolder = $folder . 'Folder';
        $folderType = $this->{$newFolder};  
        
        foreach ($this->files as $key => $val) {
            
            if ($folderType !== "") {
                $folderFilePath           =   $folderType . '/' . $val['id'];
                
                $newPictureFileName         =   $folderFilePath . '/' . $val['picture'];
                $newQrCodeFileName          =   $folderFilePath . '/' . $val['qrcode'];
                $newIniFileName             =   $folderFilePath . '/' . $val['id'] . '.ini';
                
                $currentImageFilename       =   $this->picturesFolder . '/' . $val['picture'];
                $currentQrCodeFilename      =   $this->picturesFolder . '/' . $val['qrcode'];
                $currentIniFilename         =   $this->picturesFolder . '/' . $val['id'] . '.ini';

                (!is_dir($folderFilePath)) ? mkdir($folderFilePath, 0777, true) : '';
                
                echo var_dump($folderFilePath);
                
                copy($currentImageFilename, $newPictureFileName);
                copy($currentQrCodeFilename, $newQrCodeFileName);
                $result = (copy($currentIniFilename, $newIniFileName)) ? true : false;
            }
       }
       
       return $result;
    }    
    
    /**
     * Apply the qurcode as watermark
     * 
     * @param integer $quality  Quality of the picture
     * @param string  $position Position of the watermark
     * 
     * @return boolean
     */
    public function applyQrCodeAsWatermark($quality, $position)
    {
        if (!empty($this->files)) {
            foreach ($this->files as $key => $val) {
               
                $currentImageFilename   =   $this->picturesFolder . '/' . $val['picture'];
                $qrcode                 =   $this->picturesFolder . '/' . $val['qrcode'];                
                
                $this->watImage->setImage(array('file' => $currentImageFilename, 'quality' => $quality)); // file to use and export quality
                $this->watImage->setWatermark(array('file' => $qrcode, 'position' => $position)); // watermark to use and it's position
                $this->watImage->applyWatermark();
                if (!$this->watImage->generate($currentImageFilename)) {
                    print_r($this->watImage->errors);
                }            
            }
        }
        return true;
    }
    
    /**
     * Move another directory to another
     * 
     * @param string $firstPath  First folder content
     * @param string $secondPath Folder to move to
     */
    public function moveTo($firstPath, $secondPath)
    {
        $_olderFolder           = $firstPath . 'Folder';
        $olderFolderType        = $this->{$_olderFolder};
        
        $_newFolder             = $secondPath . 'Folder';
        $newFolderType          = $this->{$_newFolder};        
        
        $files      = scandir($this->picturesFolder);
        $oldfolder  = $olderFolderType;
        $newfolder  = $newFolderType;
        foreach($files as $fname) {
            if($fname != '.' && $fname != '..') {
                rename($oldfolder.$fname, $newfolder.$fname);
            }
        }        
    }
    
    /**
     * Delete original pictures
     * 
     * @return boolean
     */
    public function delete() 
    {
        $result = true;
        
        foreach ($this->files as $key => $val) {
            
            if ($this->recoveryFolder !== "") {
                $currentImageFilename    =   $this->picturesFolder . '/' . $val['picture'];

                if (file_exists($currentImageFilename)) {
                    $result = (unlink($currentImageFilename)) ? true : false;
                }
            }
       }
       
       return $result;
    }    
    
    
    /**
     * List all pictures in this directory and apply to each a specific id and qrcode name
     * 
     * @param string $directory Directory that contains files
     * @return array
     */
    public function listDirectoryPicturesAndApplyId() 
    {
        if (is_dir($this->picturesFolder)){
            $this->files = scandir($this->picturesFolder);
        } else {
            echo 'No image directory';
        }
        
        $values = array();
        foreach ($this->files as $key => $var) {
            if (($var === '.') || ($var === '..') || (substr($var, -4, 1) !== '.')) {
                unset($this->files[$key]);
            } else  {
                $id = $this->generateRandomKey();
                $values[] = array(
                    'id' => $id, 
                    'picture' => $this->files[$key], 
                    'qrcode' => $id . $this->outputFileExtension
                );                
            }
        }
        $this->files = $values;
        return (array) $this->files;
    }

    /**
     * Generate a random key
     * https://gist.github.com/kcassam/03371e8c2e753d1cfb61
     * @return string
     */


    private function generateRandomKey()
    {
        return substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
    }
}