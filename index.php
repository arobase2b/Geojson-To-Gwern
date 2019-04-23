
<html>
<head>
<style>
    pre {outline: 1px solid #ccc; padding: 5px; margin: 5px;float:left; min-width: calc(32% - 20px); }
    .string { color: green; }
    .number { color: darkorange; }
    .boolean { color: blue; }
    .null { color: magenta; }
    .key { color: red; }
</style>
<script>
    function syntaxHighlight(json) {
        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

    function output(inp) {
        document.body.appendChild(document.createElement('pre')).innerHTML = inp;
    }
</script>
</head>
<body></body>




<?php 

ini_set('xdebug.var_display_max_depth', 15);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

set_include_path('../SERVER_SIDE/');

include 'Geojson/ParseGeojson.php';
include 'Geojson/BuildGeojson.php';
include 'Geojson/ConverterGeojson.php';

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


$testDataPolyGONLINE = '{"type":"FeatureCollection","features":[{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[[[8.165178,42.227117],[7.884534,42.227117],[7.884534,42.055116],[8.165178,42.055116],[8.165178,42.227117]]]}},{"type":"Feature","properties":{},"geometry":{"type":"LineString","coordinates":[[7.938896,41.778714],[8.16331,41.674473],[8.427872,41.874479],[8.465925,42.081489],[8.634447,42.076109]]}},{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[[[8.621763,42.316411],[9.216121,42.296309],[8.744297,42.219484],[8.64438,42.094948],[8.453297,42.224418],[8.621763,42.316411]]]}}]}';
$testHole = '{
    "type":"FeatureCollection",
    "features":[
        {
            "type":"Feature",
            "properties":{},
            "geometry":{
                "type":"MultiPolygon",
                "coordinates":[
                    [
                        [
                            [8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]
                        ],
                        [
                            [8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]
                        ]
                    ],
                    [
                        [
                            [9,42],[8.75,42],[8.75,42.939113],[9,42.939113],[9,42]
                        ],
                        [
                            [9.756,42.95],[9.77,42.95],[9.76,42.943],[9.756,42.95]
                        ]
                    ]
                ]
            }
        }, 
        
        
        
        {
            "type": "Feature",
            "properties": {},
            "geometry": {
                "type": "LineString",
                "coordinates": [
                    [
                    -124.1015625,
                    48.69096039092549
                    ],
                    [
                    53.4375,
                    49.61070993807422
                    ]
                ]
            }
        },
        
        
        
        {
            "type": "Feature",
            "properties": {},
            "geometry": {
                "type": "MultiLineString",
                "coordinates": [
                    [
                        [
                        -124.1015625,
                        48.69096039092549
                        ],
                        [
                        53.4375,
                        49.61070993807422
                        ]
                        ],
                        [
                            [
                            -124.1015625,
                            48.69096039092549
                            ],
                            [
                            53.4375,
                            49.61070993807422
                            ]
                        ]
                ]
            }
        },
        
        
        
        {
            "type": "Feature",
            "properties": {},
            "geometry": {
                "type": "GeometryCollection",
                "geometries": [
                    {
                        "type":"Polygon",
                        "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
                        
                    },
                    {
                        "type": "MultiPoint",
                        "coordinates": [ [12.6, 9.1]]
                    },
                    {
                        "type": "GeometryCollection",
                        "geometries": [
                            {
                                "type":"Polygon",
                                "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
                                
                            },
                            {
                                "type": "MultiPoint",
                                "coordinates":  [[14.6, 18.1]]
                              }
                        ]
                    }
                ]
            }
        }
        
    ]
}';

$testHoleBroken = '{
    "type":"FeatureCollection",
    "features":[
        {
            "type":"Feature",
            "properties":{"b" : ""},
            "geometry":{
                "type":"Polygon",
                "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
            }
        }, 
        {
            "type": "Feature",
            "properties": {"c" : ""},
            "geometry": {
                "type": "LineString",
                "coordinates": [
                    [
                    -124.1015625,
                    48.69096039092549
                    ],
                    [
                    53.4375,
                    49.61070993807422
                    ]
                ]
            }
        },
        {
            "type": "Feature",
            "geometry": {
              "type": "Point",
              "coordinates": [125.6, 10.1]
            },
            "properties": {
              "d" : ""
            }
        }
    ]
}';

$testFeature = '{
    "type": "Feature",
    "geometry": {
      "type": "MultiPoint",
      "coordinates": [ [12.6, 9.1], [12.6, 9.1]]
    },
    "properties": { 
        "color": "bleu",
        "nombre": 42     
    }
  }';

$testfeature2 = '{
    "type":"Feature",    
    "geometry":{
        "type":"Polygon",
        "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
        },
    
    "properties":{}
}';

$testfeaturemultipoly =
'{
    "type":"Feature",    
    "geometry":{
        "type": "MultiPolygon",
        "coordinates": [
            [
                [
                    [102.0, 2.0],
                    [103.0, 2.0],
                    [103.0, 3.0],
                    [102.0, 3.0],
                    [102.0, 2.0]
                ]
            ],
            [
                [
                    [100.0, 0.0],
                    [101.0, 0.0],
                    [101.0, 1.0],
                    [100.0, 1.0],
                    [100.0, 0.0]
                ],
                [
                    [100.2, 0.2],
                    [100.2, 0.8],
                    [100.8, 0.8],
                    [100.8, 0.2],
                    [100.2, 0.2]
                ]
            ]
        ]
    },
    
    "properties":{}
}';

$testfeature3 = '{
    "type": "Feature",    
    "geometry": {
        "type": "LineString",
        "coordinates": [
            [
            -124.1015625,
            48.69096039092549
            ],
            [
            53.4375,
            49.61070993807422
            ]
        ]
    },
    "properties": {
        "nb" : {"entier" : {"pair" : 42}}
    }
}';

$testfeaturemultiLine = '{
    "type": "Feature",    
    "geometry": {
        "type": "MultiLineString",
        "coordinates": [
            [
                [ -124.1015625, 48.69096039092549 ],
                [ 53.4375, 49.61070993807422 ]
            ],
            [
                [102.0, 2.0],
                [103.0, 3.0]
            ]
        ]
    },
    "properties": {
        "nb" : {"entier" : {"pair" : 42}}
    }
}';

$testfeaturegeometrycollection = '{
    "type": "Feature",
    "properties": {},
    "geometry": {
        "type": "GeometryCollection",
        "geometries": [
            {
                "type":"Polygon",
                "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
                
            },
            {
                "type": "MultiPoint",
                "coordinates": [ [8.921004,43.103667], [8.591314,43.092556]]
            },
            {
                "type": "GeometryCollection",
                "geometries": [
                    {
                        "type":"Polygon",
                        "coordinates":[[[8.787735,41.954007],[8.752019,41.954007],[8.752019,41.939113],[8.787735,41.939113],[8.787735,41.954007]],[[8.756502,41.950895],[8.777723,41.950784],[8.764572,41.943003],[8.756502,41.950895]]]
                        
                    },
                    {
                        "type": "MultiPoint",
                        "coordinates":  [[9.213498,42.150801],[9.418075,42.551884],[8.679231,41.944898]]
                    },
                    {
                        "type":"Polygon",
                        "coordinates":[[[9.585629,42.331966],[8.200229,42.331966],[8.200229,41.823619],[9.585629,41.823619],[9.585629,42.331966]]]
                    },
                    {
                        "type":"Polygon",
                        "coordinates":[[[8.206921,42.626483],[9.420541,42.626483],[9.420541,43.08114],[8.206921,43.08114],[8.206921,42.626483]]]
                    }
                ]
            }
        ]
    }
}';

$testteditor = '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[8.757233,42.287639],[9.23773,41.949285]]},"properties":{}},{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[8.499172,43.352651],[7.142774,43.352651],[7.142774,42.782228],[8.499172,42.782228],[8.499172,43.352651]],[[7.486004,43.178307],[8.066042,43.19132],[7.557393,42.946857],[7.486004,43.178307]]],[[[10.189022,43.003616],[9.696965,42.981001],[9.712791,42.434134],[10.176822,42.434134],[10.189022,43.003616]]]]},"properties":{"style":{"color":"#ff2600","weight":"6"}}},{"type":"Feature","properties":{"style":{"color":"#111111","weight":5,"fillColor":"#111111","fillOpacity":0.1}},"geometry":{"type":"Polygon","coordinates":[[[9.534318,43.769607],[8.150992,43.769607],[8.150992,43.517889],[9.534318,43.517889],[9.534318,43.769607]],[[8.31906,43.57403],[8.606256,43.601954],[8.57274,43.720275],[8.307313,43.689129],[8.31906,43.57403]]]}}]}';
$testeditorMulti = '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[8.757233,42.287639],[9.23773,41.949285]]},"properties":{}},{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[8.499172,43.352651],[7.142774,43.352651],[7.142774,42.782228],[8.499172,42.782228],[8.499172,43.352651]],[[7.486004,43.178307],[8.066042,43.19132],[7.557393,42.946857],[7.486004,43.178307]]],[[[10.189022,43.003616],[9.696965,42.981001],[9.712791,42.434134],[10.176822,42.434134],[10.189022,43.003616]]]]},"properties":{"style":{"color":"#ff2600","weight":"6"}}},{"type":"Feature","properties":{"style":{"color":"#111111","weight":5,"fillColor":"#111111","fillOpacity":0.1}},"geometry":{"type":"Polygon","coordinates":[[[9.534318,43.769607],[8.150992,43.769607],[8.150992,43.517889],[9.534318,43.517889],[9.534318,43.769607]],[[8.31906,43.57403],[8.606256,43.601954],[8.57274,43.720275],[8.307313,43.689129],[8.31906,43.57403]]]}},{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[7.138312,43.91564],[5.875281,43.91564],[5.875281,43.504945],[7.138312,43.504945],[7.138312,43.91564]]],[[[6.223635,43.26174],[5.06802,43.26174],[5.06802,42.870582],[6.223635,42.870582],[6.223635,43.26174]]]]},"properties":{"style":{"color":"#00f900","weight":"10"}}},{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[8.55618,44.547192],[10.911151,42.857635],[10.869939,44.27804]]},"properties":{}},{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[8.917781,43.089431],[9.041045,43.17029]]},"properties":{}},{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[2.280251,46.088185],[0.799743,46.088185],[0.799743,45.021968],[2.280251,45.021968],[2.280251,46.088185]]],[[[4.307831,45.726018],[3.169138,45.726018],[3.169138,44.442504],[4.307831,44.442504],[4.307831,45.726018]]]]},"properties":{}}]}';
$geostringtest = '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[8.757233,42.287639],[9.23773,41.949285]]},"properties":{}},{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[7.446919,42.082642],[10.848242,42.751978],[6.88196,43.045253],[8.36489,43.139838],[9.494741,43.53379],[7.529271,43.865791]]},"properties":{}}]}';
/*
try{   
    $pattern = '#\s*#m';
    $replace = '';
    $removedLinebaksAndWhitespace = preg_replace( $pattern, $replace,$geostringtest);
    ?>    
    <script>        
        var geo = JSON.parse('<?php echo $removedLinebaksAndWhitespace?>');        
        var geoIndente = JSON.stringify(geo, undefined, 4);
        output(syntaxHighlight(geoIndente));
        </script>
    <?php
    
    //Montre le geojson en objet php parsé
     
    $a = new Geojson\Parse\GeoJSONParser($geostringtest);
    //var_dump($a);

    //Montre le geojson objet exporté en geojson string
     
    $res = $a->geojson->exportToString();
    ?>    
    <script>
        var geo = JSON.parse('<?php echo  $res ?>');        
        var geoIndente = JSON.stringify(geo, undefined, 4);
        output(syntaxHighlight(geoIndente));

        if(json_last_error() != ""){
            <?php $a->geojson->exportToString() ?>
        }
        </script>
    <?php    
    var_dump($a);
   

}catch (\LogicException $e){
    echo "Erreur pour le developpeur : <br\n";
    echo $e->getMessage()."\n";
    echo $e->getTraceAsString();
}
catch (Exception $e){
    echo "Erreur pour l'utilisateur : \n";
    echo $e->getMessage()."\n";
}
*/
try{
    /*
    $a = new Geojson\Parse\GeoJSONParser($testeditorMulti);
    $b = new Geojson\Converter\GeojsonToShap($a->geojson,"editormulti2");
    var_dump($a->geojson);
    $b->setPathogr2ogr("/Library/Frameworks/GDAL.framework/Versions/Current/Programs/ogr2ogr");
    $b->generateShapeFiles();
    */

    $c = new Geojson\Converter\ShapToGeojson("CORSE3villes_wgs84_SHP_5b9144772e967_2018-09-06__17_15_03");
    $c->setPathogr2ogr("/Library/Frameworks/GDAL.framework/Versions/Current/Programs/ogr2ogr");
    $c->generateGeoJsonFiles();

}catch (\LogicException $e){
    echo "Erreur pour le developpeur : <br\n";
    echo $e->getMessage()."\n";
    echo $e->getTraceAsString();
}
catch (Exception $e){
    echo "Erreur pour l'utilisateur : \n";
    echo $e->getMessage()."\n";
}

?>

