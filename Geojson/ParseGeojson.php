<?php
namespace Geojson\Parse;

/**
 * Classe convertissant une string Geojson en un objet Geojson manipulable
 */
class GeoJSONParser
{
    public $geojson;
    /**
     * Reçoit le geojson sous forme de string et le parse.  
     *
     * @param string $geojson
     */
    public function __construct(string $geojson)
    {        
        $this->_struct = json_decode($geojson);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON est valide 
                break;
            case JSON_ERROR_DEPTH:
                throw new \Exception('The maximum stack depth has been exceeded.');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw new \Exception('Invalid or malformed JSON.');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new \Exception('Control character error, possibly incorrectly encoded.');
                break;
            case JSON_ERROR_SYNTAX:
                throw new \Exception('Syntax error, malformed JSON.');
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                throw new \Exception('Malformed UTF-8 characters, possibly incorrectly encoded.');
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                throw new \Exception('One or more recursive references in the value to be encoded.');
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                throw new \Exception('One or more NAN or INF values in the value to be encoded.');
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                throw new \Exception('A value of a type that cannot be encoded was given.');
                break;
            default:
                throw new \Exception('Unknown JSON error occured.');
                break;
        }
        $this->parse();
    } 
    
    /**
     * Parse le geojson, Feature Et FeatureCollection. L'objet geojson est accessible via ->geojson
     * Detruit la variable _struct
     *
     * @return void
     */
    public function parse()
    {
        if ($this->_struct->type == 'Feature') {
            $this->geojson =  new \Geojson\Build\GeoJSON("Feature");

            if(!isset($this->_struct->geometry)){throw new \Exception('Missing feature geometry');};
            if(!isset($this->_struct->properties)){throw new \Exception('Missing feature properties');};

            $this->addFeature((array)$this->_struct->properties,$this->_struct->geometry);
        }else
        if ($this->_struct->type == 'FeatureCollection') {
            $this->geojson =  new \Geojson\Build\GeoJSON("FeatureCollection");
            foreach ($this->_struct->features as $key => $value) {
                assert(isset($value->geometry), new \Exception('Missing feature geometry'));
                assert(isset($value->properties), new \Exception('Missing feature properties'));

                $this->addFeature((array)$value->properties,$value->geometry);
            }            
           
        }else{
            throw new \Exception('Type of geojson invalid, Feature or FeatureCollection only');
        }
        unset($this->{'_struct'});
    }
    /**
     * Verifie qu'un array est bien associatif
     *
     * @param array $arr
     * @return void
     */
    private function __isAssoc(array $arr){
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Ajout d'une feature au geojson
     *
     * @param [type] $properties
     * @param [type] $geometry
     * @return void
     */
    public function addFeature($properties, $geometry){
        !is_array($properties) ? new \Exception('Properties format is not an array') : '';
       
        
        $this->geojson->addFeature($properties, GeoJSONParser::validGeometryJson($geometry));
        
       
        if(count($properties)>0){
            if(!$this->__isAssoc($properties)){
                throw new \Exception('Properties format must be a Json Object');
            }
        }
        
    }
    /**
     * Valide une géometry et la retourne. La fonction est statique car utilisé par le \Geojson\Build\Geometry pour
     * valider les GeometryCollection
     *
     * @param \stdClass $geometry
     * @return void
     */
    public static function validGeometryJson(\stdClass $geometry):\Geojson\Build\Geometry
    {
        if(!isset($geometry)){
            throw new \Exception('Missing in the feature geometry');
        }

        if($geometry->type != "GeometryCollection"){
            if(!isset($geometry->coordinates)){throw new \Exception('Missing Coordinate in the geometry');};
        }else{
            if(!isset($geometry->geometries)){throw new \Exception('Missing geometries in the GeometryCollection');};
        }

        if ($geometry->type == 'Point') {
            $point = new \Geojson\Build\Geometry("Point");
            $point->setGeo($geometry->coordinates);
            return $point;
        }else        
        if ($geometry->type == 'MultiPoint') {
            $multiPoint = new \Geojson\Build\Geometry("MultiPoint");
            $multiPoint->setGeo($geometry->coordinates);
            return $multiPoint;
        }else
        if ($geometry->type == 'Polygon') {
            
            $polygon = new \Geojson\Build\Geometry("Polygon");
            $polygon->setGeo($geometry->coordinates);
            return $polygon;
                    
        }else
        if ($geometry->type == 'MultiPolygon') {            
            $MultiPolygon = new \Geojson\Build\Geometry("MultiPolygon");
            $MultiPolygon->setGeo($geometry->coordinates);
            return $MultiPolygon;
                    
        }else
        if ($geometry->type == 'LineString') {
            $lineString = new \Geojson\Build\Geometry("LineString");
            $lineString->setGeo($geometry->coordinates);
            return $lineString;

        }else
        if ($geometry->type == 'MultiLineString') {
            $MultiLineString = new \Geojson\Build\Geometry("MultiLineString");
            $MultiLineString->setGeo($geometry->coordinates);
            return $MultiLineString;

        }else
        if ($geometry->type == 'GeometryCollection') {
            $GeometryCollection = new \Geojson\Build\Geometry("GeometryCollection");
            $GeometryCollection->setGeo($geometry->geometries);
            return $GeometryCollection;

        }else        
        if($geometry->type){
            throw new \Exception('Unsupported geometry type');
        }
    }
}
