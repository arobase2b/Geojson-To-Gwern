<?php
/**
 * Permet de convertir des Geojson en ShapeFile et inversement 
 */
namespace Geojson\Converter;

Class GeojsonToShap{

    private $originGeoJson = null;
    private $originNameUnsafe = null;
    private $labelNameFile = null;
    /**
     * Nom du fichier/dossier du geojson/shpfile generé
     *
     * @var string
     */
    public  $folderName = "";
    private $dP = "shp_folder/";
    public  $dd = "";
    public  $geojsonpath = "";

    private $pathogr2ogr = "ogr2ogr";
    private $encodageSortie = "-t_srs EPSG:2154";

    /**
     * A partir d'un (Geojson) Geojson et d'un nom, génère un dossier avec le fichier geojson écrit dedans.
     * Il est nécessaire d'appeler la fonction generateShapeFiles() après l'instanciation
     *
     * @param \Geojson\Build\GeoJSON $geojson
     * @param \string $name
     */
    public function __construct(\Geojson\Build\GeoJSON $geojson, string $name)
    {
        $this->setOriginGeoJson($geojson);
        $this->setOriginNameUnsafe($name);

        $this->dP = get_include_path().$this->dP;

        $tname = \Geojson\Converter\GeojsonToShap::filterName($this->getOriginNameUnsafe());
        if($tname != ""){
            $this->setName($tname);
        }else{
            throw new \Exception("The shapefile name is not valid, aZ09 [3 - 30]");
        }
        $this->generateFolderName();
        $this->mkdirFolder();
        $this->writeGeoJsonFileInFolder();
    }   
    /**
     * Execution des commande OGR pour generer les .shp à partir du geojson, dans le dossier $dd
     *
     * @return void
     */
    public function generateShapeFiles(){
        exec("
            $this->pathogr2ogr $this->encodageSortie -nlt POLYGON      -skipfailures $this->dd/$this->labelNameFile\_POLYGON.shp     $this->geojsonpath ;
            $this->pathogr2ogr $this->encodageSortie -nlt MULTIPOLYGON      -skipfailures $this->dd/$this->labelNameFile\_MULTIPOLYGON.shp     $this->geojsonpath ; 
            $this->pathogr2ogr $this->encodageSortie -nlt LINESTRING   -skipfailures $this->dd/$this->labelNameFile\_LINESTRING.shp     $this->geojsonpath ;
            $this->pathogr2ogr $this->encodageSortie -nlt POINT        -skipfailures $this->dd/$this->labelNameFile\_POINT.shp     $this->geojsonpath;
            $this->pathogr2ogr $this->encodageSortie -nlt MULTIPOINT        -skipfailures $this->dd/$this->labelNameFile\_MULTIPOINT.shp     $this->geojsonpath ");          
    }

    public function zipFiles():string{
        require_once __DIR__ ."/../ZipFolder.php";
        $a = new \ZipFolder($this->dd);
        return $this->dd.'.zip';
    }
    
    /**
     * Ecriture du fichier geojson dans le dossier de destination $dd
     *
     * @return void
     */
    public function writeGeoJsonFileInFolder(){        

        $this->geojsonpath = "$this->dd/$this->labelNameFile.geojson";
        $fp = fopen($this->geojsonpath, 'w');
        
        fwrite($fp, $this->originGeoJson->exportToString());
        fclose($fp);
    }
    /**
     * Creation du nom unique
     *
     * @return void
     */
    public function generateFolderName()
    {
        $this->folderName = $this->getName()."_wgs84_".uniqid("SHP_")."_".date("Y-m-d__H_i_s");
    }
    /**
     * Creer le dossier final et genere le chemin ($dd)
     *
     * @return void
     */
    public function mkdirFolder(){
        $this->dd = $this->dP.$this->folderName;
        mkdir($this->dd);
    }

    /**
     * Permet de verifier si un nom de fichier est valide ("aZ09" et d'une longueur 3 à 30 caracteres)
     * Les espaces sont nettoyés
     *
     * @param string $name
     * @return string
     */
    public static function filterName(string $name):string
    {
        $name = str_replace(' ', '', $name);
        if(preg_match("/^[a-zA-Z0-9]{3,30}+$/", $name) == 1) {
            return $name;
        }else{
            return "";
        }
    }
    /**
     * Set le geojson original
     *
     * @param \Geojson\Build\GeoJSON $geo
     * @return void
     */
    private function setOriginGeoJson(\Geojson\Build\GeoJSON $geo)
    {
        if($this->originGeoJson == null){
            $this->originGeoJson = $geo;
        }else{throw new \LogicException("Variable \$originGeoJson is read-only");}
        
    }

    /**
     * Set le nom de fichier saisi
     *
     * @param string $name
     * @return void
     */
    private function setOriginNameUnsafe(string $name)
    {
        if($this->originNameUnsafe == null){
            $this->originNameUnsafe = $name;
        }else{throw new \LogicException("Variable \$originNameUnsafe is read-only");}
    }

    /**
     * retourne le geojson original
     *
     * @return \Geojson\Build\GeoJSON
     */
    public function getOriginGeoJson():\Geojson\Build\GeoJSON
    {
        return $this->originGeoJson;
    }

    /**
     * retourne le nom de fichier original
     *
     * @return string
     */
    public function getOriginNameUnsafe():string
    {
        return $this->originNameUnsafe;
    }

    /**
     * Retourne le nom filtré du fichier
     *
     * @return string
     */
    public function getName():string{
        return $this->labelNameFile;
    }

    /**
     * Set le nom filtré du fichier
     *
     * @param \string $name
     * @return void
     */
    private function setName(string $name){
        if($name != null){
            $this->labelNameFile = $name;
        }else{throw new \LogicException("Variable \$labelNameFile is read-only");}
    }

    /**
     * Change le chemin de la commande ogr du system (defaut : ogr2ogr)
     *
     * @param string $path
     * @return void
     */
    public function setPathogr2ogr(string $path)
    {
        $this->pathogr2ogr = $path;
    }

    /**
     * Retourne le path ogr utilisé
     *
     * @return string
     */
    public function getPathogr2ogr():string
    {
        return $this->pathogr2ogr;
    }

}
/**
 * 
 */
Class ShapToGeojson{

    public $shpfolder;
    public $folderscan;
    public $pathfolder;
    
    private $dP = "shp_folder/";
    private $listSHP = array();

    private $pathogr2ogr = "ogr2ogr";
    private $encodageSortie = "-t_srs EPSG:2154";    

    /**
     * Vérifie que le dossier existe bien
     *
     * @param string $shpfolder chemin vers le dossier
     */
    public function __construct(string $shpfolder){        
        $this->dP = get_include_path().$this->dP;
        if(!is_dir($this->dP.$shpfolder)){
            throw new \Exception("The folder doesn't exist");
        }
        $this->pathfolder = $this->dP.$shpfolder;      

    }
    /**
     * Scan le dossier et convertit chaque .SHP en .geojson avec le meme nom
     *
     * @return void
     */
    public function generateGeoJsonFiles(){
        $this->folderscan = scandir($this->pathfolder);
               
        foreach ($this->folderscan as $value) {
            if(pathinfo($value)["extension"] == "shp"){
                $listSHP [] = $value;
            }            
        };
        $command = "";
        foreach ($listSHP as  $value) {
            # code...
            $basename = pathinfo($value)["basename"];
            $filename = pathinfo($value)["filename"];
            $command .= "$this->pathogr2ogr $this->encodageSortie -f GeoJSON $this->pathfolder/$filename.geojson $this->pathfolder/$basename;";          
   
        }
        exec($command);
    }

    /**
     * Change le chemin de la commande ogr du system (defaut : ogr2ogr)
     *
     * @param string $path
     * @return void
     */
    public function setPathogr2ogr(string $path)
    {
        $this->pathogr2ogr = $path;
    }

    /**
     * Retourne le path ogr utilisé
     *
     * @return string
     */
    public function getPathogr2ogr():string
    {
        return $this->pathogr2ogr;
    }
    
}

/**
 * 
 */
Class KMLToGeojson{

    public $kmlfilename;
    public $kmlfilewithextension;
    public $folderscan;
    public $pathfolder;
    
    private $dP = "Upload_";
    

    private $pathogr2ogr = "ogr2ogr";
    private $encodageSortie = "-t_srs EPSG:4326";    

    /**
     * Vérifie que le dossier existe bien
     *
     * @param string $kmlfolder chemin vers le dossier
     */
    public function __construct(string $kmlfilename, string $kmlfilewithextension){        
        $this->dP = get_include_path().$this->dP;       
        $this->pathfolder = $this->dP;      

        $this->kmlfilename = $kmlfilename;
        $this->kmlfilewithextension = $kmlfilewithextension;

    }
    /**
     * Scan le dossier et convertit chaque .SHP en .geojson avec le meme nom
     *
     * @return void
     */
    public function generateGeoJsonFiles(){        
              
        $command = "$this->pathogr2ogr $this->encodageSortie -f GeoJSON $this->pathfolder/$this->kmlfilename.geojson $this->pathfolder/$this->kmlfilewithextension;";        
        exec($command);
    }

    /**
     * Change le chemin de la commande ogr du system (defaut : ogr2ogr)
     *
     * @param string $path
     * @return void
     */
    public function setPathogr2ogr(string $path)
    {
        $this->pathogr2ogr = $path;
    }

    /**
     * Retourne le path ogr utilisé
     *
     * @return string
     */
    public function getPathogr2ogr():string
    {
        return $this->pathogr2ogr;
    }
    
}

Class SHPToGeojson{

    public $shpfilename;
    public $shpfileextensions;
    public $folderscan;
    public $pathfolder;
    
    private $dP = "Upload_";
    

    private $pathogr2ogr = "ogr2ogr";
    private $encodageSortie = "-t_srs EPSG:4326";    

    /**
     * Vérifie que le dossier existe bien
     *
     * @param string $kmlfolder chemin vers le dossier
     */
    public function __construct(array $shpfilename, array $shpfileextensions){        
        $this->dP = get_include_path().$this->dP;       
        $this->pathfolder = $this->dP;
        $this->shpfilename = $shpfilename;
        $this->shpfileextensions = $shpfileextensions;
    }

    /**
     * Vérifie l'existence des fichiers .shp, .dbf .shx, .prj
     *
     * @return boolean
     */
    private function shapeIsValid():bool{

        $attendu = array("shp", "dbf", "shx", "prj");
        if(\count($this->shpfileextensions) > 3){
            $ok = true;
            foreach ($attendu as $value) {
                if(!\in_array($value, $this->shpfileextensions)){
                    $ok = false;
                }
            }
            return $ok;
        }
        return false;
    }

    /**
     * Scan le dossier et convertit chaque .SHP en .geojson avec le meme nom
     *
     * @return void
     */
    public function generateGeoJsonFiles(){        
        if($this->shapeIsValid()){
            $command = "$this->pathogr2ogr $this->encodageSortie -f GeoJSON $this->pathfolder/".$this->shpfilename[0].".geojson $this->pathfolder/".$this->shpfilename[0].".shp;";        
            exec($command);
        }else{
            throw new \LogicException('Extension minimum attendues : "shp", "dbf", "shx", "prj"');
        }
       
    }

    /**
     * Change le chemin de la commande ogr du system (defaut : ogr2ogr)
     *
     * @param string $path
     * @return void
     */
    public function setPathogr2ogr(string $path)
    {
        $this->pathogr2ogr = $path;
    }

    /**
     * Retourne le path ogr utilisé
     *
     * @return string
     */
    public function getPathogr2ogr():string
    {
        return $this->pathogr2ogr;
    }
    
}
