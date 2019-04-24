<?php

namespace POSTGRESTOGWERN\PostgresToGwern;

include 'zipFolder.php';
/**
 * Permet de generer un fichier SHP avec ses données attributaires
 */
class PostgresToGwern{
    private $pg_host;
    private $pg_userName;
    private $pg_password;

    private $tempDestinationFolder;
    private $destinationFolder;

    private $globalTmp = "tmp";

    /**
     * Instanciation de PostgresToGwern, permet de générer un fichier SHP 
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $destinationFolder chemin où sera sauvegarder le zip contenant de le shp
     */
    public function __construct(string $host,string $username,string $password, string $destinationFolder){
        $this->pg_host = $host;
        $this->pg_userName = $username;
        $this->pg_password = $password;
        $this->destinationFolder = $destinationFolder;        
    }

    /**
     * Execute la requete passé en paramètre.
     * Nécessite que le filename soit configuré 
     *
     * @param string $queryString requete postgresql
     * @return bool
     */
    public function query(string $filename,string $tablename, string $queryString){
        if(self::isFnameValid($filename)){
            $uniqueName = $this->generateUniqueName($filename);
            $tempFolder = "$this->globalTmp/$uniqueName";
            mkdir($tempFolder);
            exec("cd $tempFolder && pgsql2shp -f '$filename' -k -h $this->pg_host -u $this->pg_userName -P $this->pg_password $tablename $queryString ");
            
            $ZF = new zipFolder($tempFolder);
            rename("$tempFolder.zip", "$this->destinationFolder/$uniqueName.zip");
        }
        return false;
    }

    public function setGlobalTmp(string $foldername){
        $this->globalTmp($foldername);
    }

    /**
     * Genere un nom unique.
     *
     * @param [type] $filename
     * @return string
     */
    private function generateUniqueName($filename):string{
        return $filename."_".\uniqid()."_".time();
    }


    /**
     * Verifie qu'une string peut être un nom de fichier valide,( lettre chiffre _ - de 3 à 60 char)
     *
     * @param string $fname
     * @return boolean
     */
    public static function isFnameValid(string $fname):bool{
        $t = preg_match('/^[a-z-A-Z0-9_-]{3,60}$/', $fname, $matches);
        if($t) {
            return true;
        } else {
            return false;
        }
    }
}