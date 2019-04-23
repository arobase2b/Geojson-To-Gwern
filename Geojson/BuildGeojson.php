<?php
/**
 * Fichier permettant d'acceder aux classes GeoJSON Feature FeatureCollection Geometry Point Polygon LineString
 */
namespace Geojson\Build;
/**
 * Objet Geojson rfc7946 représentant un fichier, feature ou feature collection
 * https://tools.ietf.org/html/rfc7946
 */
class GeoJSON
{
    public $feature;
    public $featureCollection;
    public $type;

    /**
     * Création d'un geojson, définition de son type "Feature" ou "FeatureCollection"
     *
     * @param string $type
     */
    public function __construct($type = "Feature")
    {
        if($type == "FeatureCollection"){
            $this->type = "FeatureCollection";
            $this->featureCollection = new \Geojson\Build\FeatureCollection();
        }else{
            $this->type = "Feature";
        }
    }
    /**
     * Ajout d'une feature au geojson, transforme le geojson en FeatureCollection si il y a 2 features
     *
     * @param array $properties
     * @param Geometry $geometry
     * @return void
     */
    public function addFeature(array $properties, Geometry $geometry){
        
        if($this->type == "Feature"){
            //Le geojson a été crée comme feature et non feature collection, on le modifie
            if($this->feature == null){
                //Si la feature n'avait pas été créée; (1er appel de addFeature)
                $this->feature = new \Geojson\Build\Feature($properties, $geometry);
            }else{
                //Une feature existait déjà dans ce geojson de non collection
                $this->featureCollection = new \Geojson\Build\FeatureCollection();
                $this->featureCollection->addFeature($this->feature);                
                $this->feature = null; 
                $this->type = "FeatureCollection";

                $this->featureCollection->addFeature(new \Geojson\Build\Feature($properties, $geometry));
            } 
        }else{
            $this->featureCollection->addFeature(new \Geojson\Build\Feature($properties, $geometry));
        }

    }

    /**
     * Transform l'objet php Geojson en objet Geojson javascript
     *
     * @return string
     */
    public function exportToString(): string
    {
        $geoJsonInArray= array();
        if($this->type == "Feature"){
            $geoJsonInArray["type"] = "Feature";
            $geoJsonInArray["properties"] = $this->feature->properties;

            //Transforme la liste [] en objet json {} si les propriétés sont vides
            if(count($geoJsonInArray["properties"]) == 0){
                $geoJsonInArray["properties"] =  (object) array();
            }
            
            $geoJsonInArray["geometry"] = array();
            $geoJsonInArray["geometry"]["type"] = $this->feature->geometry->type;
            if($this->feature->geometry->type == "GeometryCollection"){
                $geoJsonInArray["geometry"]["geometries"] = $this->feature->geometry->exportToArray();
            }else{
                $geoJsonInArray["geometry"]["coordinates"] = $this->feature->geometry->exportToArray();
            }
            
           return JSON_ENCODE($geoJsonInArray);
        }else
        if($this->type == "FeatureCollection"){
            $geoJsonInArray["type"] = "FeatureCollection";
            $geoJsonInArray["features"] = [];
            foreach ($this->featureCollection->getFeatures() as $f) {
                $featureInArray = [];

                $featureInArray["type"] = "Feature";
                $featureInArray["properties"] = $f->properties;

                //Transforme la liste [] en objet json {} si les propriétés sont vides
                if(count($featureInArray["properties"]) == 0){
                    $featureInArray["properties"] =  (object) array();
                }
                
                $featureInArray["geometry"] = array();
                $featureInArray["geometry"]["type"] = $f->geometry->type;
                if($f->geometry->type == "GeometryCollection"){
                    $featureInArray["geometry"]["geometries"] = $f->geometry->exportToArray();
                }else{
                    $featureInArray["geometry"]["coordinates"] = $f->geometry->exportToArray();
                }
                $geoJsonInArray["features"] [] = $featureInArray;
            }
            return JSON_ENCODE($geoJsonInArray);
        } 
    }
}

/**
 * Collection de feature, c'est un type de geojson
 */
class FeatureCollection
{
    private $features;
    public function __construct(){}

    /**
     * Ajoute une feature à la feature collection
     *
     * @param Feature $feature
     * @return void
     */
    public function addFeature(Feature $feature)
    {
        $this->features [] = $feature;
    }

    /**
     * Retourne l'ensemble des features
     *
     * @return array
     */
    public function getFeatures() : array
    {
        return $this->features;
    }    
}

/**
 * Feature geojson, composé de geometry et de properties
 */
class Feature
{
    public $geometry;
    public $properties;
    public function __construct(array $properties,Geometry $geometry)
    {
        $this->properties = $properties;
        $this->geometry = $geometry;
    }
}
/**
 * Geometry objet, (Point Polygon LineString MultiPoint MultiPolygon MultiLineString GeometryCollection)
 */
class Geometry
{
    public $coordinates;
    public $type;
    public $geometries;
    /**
     * Creation d'une geometry, type Point Polygon LineString MultiPoint MultiPolygon MultiLineString GeometryCollection
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if(
            $type === "Polygon"|| $type == "MultiPolygon" || 
            $type === "LineString" || $type == "MultiLineString" || 
            $type === "Point" || $type == "MultiPoint" ||
            $type = "GeometryCollection"){
            $this->type = $type;
        }else{
            throw new \LogicException('Type of geometry invalid');
        }

    }
    /**
     * definit les coordonnées de la géometrie, ou un tableau de géometrie si "GeometryCollection"
     *
     * @param array $coordinates
     * @return void
     */
    public function setGeo(array $coordinates)
    {
        if($this->type === "Point"){
            if(count($coordinates) === 2){
                $this->coordinates = new \Geojson\Build\Point($coordinates[0], $coordinates[1]);
            }else{
                throw new \LogicException("Invalid Point coordinates...[float x,float y] expected ");
            }
        }if($this->type === "MultiPoint"){
            foreach ($coordinates as $value) {
                if(count($value) === 2){
                    $this->coordinates [] = new \Geojson\Build\Point($value[0], $value[1]);
                }else{
                    throw new \LogicException("Invalid Point coordinates...[float x,float y] expected ");
                }
            }
        }        
        else 
        if($this->type == "Polygon"){
            $this->coordinates = new \Geojson\Build\Polygon($coordinates);
        }else 
        if($this->type == "MultiPolygon"){
            foreach ($coordinates as $coordinate) {
                $this->coordinates [] = new \Geojson\Build\Polygon($coordinate);
            }
        }
        else 
        if($this->type == "LineString"){
            $this->coordinates = new \Geojson\Build\LineString($coordinates);
        }else 
        if($this->type == "MultiLineString"){
            foreach ($coordinates as $coordinate){
                $this->coordinates [] = new \Geojson\Build\LineString($coordinate);
            }            
        }else 
        if($this->type == "GeometryCollection"){
            unset($this->{'coordinates'});

            foreach ($coordinates as $geometry){
                
                $this->geometries [] = \Geojson\Parse\GeoJSONParser::validGeometryJson($geometry);
                
            }            
        }
    }

    /**
     * Transforme les geometries en tableau pour l'exportToString()
     *
     * @return array
     */   
    public function exportToArray():array
    {   
        if($this->type === "Point" || $this->type == "Polygon" || $this->type == "LineString"){
            return $this->coordinates->toArray();
        }else
        if($this->type === "MultiPoint" || $this->type == "MultiPolygon" || $this->type == "MultiLineString" ){
            $tmpCoordinatesArray = array();
            foreach ($this->coordinates as $coordinate) {
                $tmpCoordinatesArray [] = $coordinate->toArray();
            }
            return $tmpCoordinatesArray;
        }else 
        if($this->type == "GeometryCollection"){            
            $tmpCoordinatesArray = array();
            foreach ($this->geometries as $geometry){  
                $tmpgeo = [];              
                $tmpgeo["type"] = $geometry->type;
                if($geometry->type == "GeometryCollection"){
                    $tmpgeo["geometries"] = $geometry->exportToArray();
                }else{
                    $tmpgeo["coordinates"] = $geometry->exportToArray();
                }

                $tmpCoordinatesArray [] = $tmpgeo;
            }  
            return $tmpCoordinatesArray;          
        }
    }
}

/**
 * Classe definissant un point sur la carte, elle est utilisé par toutes les géometries
 */
class Point
{
    private $_x;
    private $_y;
    /**
     * Instancie un point avec une position x et y
     *
     * @param float $x
     * @param float $y
     */
    public function __construct(float $x, float $y)
    {
        $this->setLongitude($x);
        $this->setLatitude($y);       
    }
    /**
     * Retourne la latitude (y)
     *
     * @return float
     */
    public function latitude():float
    {
        return $this->_y;
    }
    /**
     * Retourne la longitude (x)
     *
     * @return float
     */
    public function longitude():float
    {
        return $this->_x;
    }
    /**
     * Definit la latitude (y) - 90 à +90
     *
     * @param float $y
     * @return void
     */
    public function setLatitude(float $y)
    {
        if($y >= -90 && $y <= 90){
            $this->_y = $y;
        }else{
            throw new \LogicException('Invalid latitude degree');
        }
    }
    /**
     * Definit la longitude (x) -180 à +180
     *
     * @param float $x
     * @return void
     */
    public function setLongitude(float $x)
    {
        assert($x >= -180 && $x <= 180, new \LogicException('Invalid longitude degree'));
        $this->_x = $x;
    }
    /**
     * Retourne la longitude latitude sous forme d'une liste [x, y] pour la convertion en string
     *
     * @return array
     */
    public function toArray():array
    {
        return [$this->longitude(), $this->latitude()];
    }
    /**
     * Compare deux points entre eux et retourne si ils sont identiques
     *
     * @param Point $point1
     * @param Point $point2
     * @return boolean
     */
    public static function areTwoPointsIdentical(Point $point1,Point $point2):bool
    {
        if($point1->latitude() === $point2->latitude() && $point1->longitude() === $point2->longitude()){
            return true;
        }
        return false;
    }    
}
/**
 * Classe définissant un polygon, il peut etre composé de trous, chaque point du polygon est une instance de \Geojson\Build\Point 
 */
class Polygon
{
    private $_points;
    public $_holes;
    /**
     * Un polygon est constitué d'une liste composé de
     *
     * @param array $points
     */
    public function __construct(array $points)
    {
        $c =  count($points);
        if($c === 0){
            new \LogicException('Polygon coordinates are empty, must be an array of array of xy array');
        }else{

            //parcour le polygon
            //[0] est le polygon
            //[0+n] sont les trous
            for($i = 0; $i < $c; $i++){
                if(gettype($points[$i]) === "array"){
                    $cc = count($points[$i]);

                    //Verifie que le polygon ou trou a bien 4 positions
                    if($cc < 4){
                        throw new \LogicException("Coordinates not valid, 4 positions minimum expected $cc given");
                    }else{
                        $tmpPoint = array();
                        for($ii = 0; $ii < $cc; $ii++){

                            if(count($points[$i][$ii]) === 2){
                                $tmpPoint [] = new \Geojson\Build\Point($points[$i][$ii][0], $points[$i][$ii][1]);
                            }else{
                                throw new \LogicException("Invalid Point coordinates...[float x,float y] expected during Polygon Build, position [$i [$ii]] ");
                            }
                            
                        }
                        if(!\Geojson\Build\Point::areTwoPointsIdentical($tmpPoint[0], $tmpPoint[$cc -1])){
                            throw new \LogicException("First and Last position must be identical in polygons");
                        }
                    }
                }else{
                    throw new \LogicException("Coordinates must be an array of array[x,y] 4 minimum");
                }

                if($i === 0){                         
                    $this->_points = $tmpPoint;
                }else{
                    $this->_holes [] = $tmpPoint;
                }
            }
            
        }
    } 
    /**
     * Ajoute un trou dans le polygon, un trou est une liste de 4 coordonnée, comme un polygon
     *
     * @param array $hole
     * @return void
     */
    public function addHole(array $hole)
    {
        $c = count($hole);

        if($c < 4){
            throw new \LogicException("Coordinates not valid, 4 positions minimum expected $c given");
        }else{
            $tmpPoint = array();
            for($i = 0; $i < $c; $i++){

                if(count($hole[$i]) === 2){
                    $tmpPoint [] = new \Geojson\Build\Point($hole[$i][0], $hole[$i][1]);
                }else{
                    throw new \LogicException("Invalid Point coordinates...[float x,float y] expected adding holes, position [$i] ");
                }
                
            }
        }
        $this->_holes[] = $tmpPoint;
    }
    /**
     * Converti le polygons et ses trous en array pour la conversion en string
     *
     * @return array
     */
    public function toArray():array
    {
        $arrayCoordinates = [];
        $_points = [];
        foreach ($this->_points as $point) {
            $_points [] = $point->toArray();
        }
        $arrayCoordinates [] = $_points;

        if($this->_holes != null){
            foreach($this->_holes as $hole){
                $__points = [];
                foreach($hole as $point){
                    $__points [] = $point->toArray();
                }
                $arrayCoordinates [] = $__points;
            }
        }
        

        return $arrayCoordinates;
    }
}

/**
 * Classe définissant les chemins de points, linestring. Composé de Points
 */
class LineString
{
    private $_points;

    /**
     * Construit une lineString à partir d'un 
     *
     * @param array $points
     */
    public function __construct(array $points = [])
    {
        $c =  count($points);
        if($c === 0){
            new \LogicException('LineString coordinates are empty, must be an array of array of xy array');
        }else
        if($c === 1){
            new \LogicException("Coordinates not valid, 2 positions minimum expected $c given");
        }else{
            $this->addPoints($points);
        }
    }
    /**
     * Ajout de plusieurs point à une lineString, convertie une liste de [x,y] en Point
     *
     * @param array $points
     * @return void
     */
    public function addPoints(array $points)
    {
        foreach ($points as $point) {
            list($x, $y) = $point;
            $this->addPoint(new \Geojson\Build\Point($x, $y));
        }
    }
    /**
     * Ajout un point à la linestring
     *
     * @param Point $point
     * @return void
     */
    public function addPoint(Point $point)
    {
        $this->_points[] = $point;
    }

    public function toArray()
    {
        $arrayCoordinates = [];
        foreach($this->_points as $point){
            $arrayCoordinates [] = $point->toArray();
        }

        return $arrayCoordinates;
    }
    
}

