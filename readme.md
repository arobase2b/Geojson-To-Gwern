PostgresToGwern
=================

Exemples :
==========
```php

/**
     * Instanciation de PostgresToGwern, permet de générer un fichier SHP 
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $destinationFolder chemin où sera sauvegarder le zip contenant de le shp
     */
$pTG = new PostgresToGwern("localhost", "postgres", "root", "downloadShpZip");

/**
     * Execute la requete passée en paramètre et génère un ZIP contenant le SHP.
     * 
     * @param string $filename
     * @param string $dbname
     * @param string $queryString
     * @return bool
     */
$pTG->query("test1", "gwern1", "\"SELECT * FROM ".'\"zh2-polygon\"'." WHERE id='idtutu'\"");

```
Requirements :
==============

Php 7.1

postgresql 11

pgsql2shp dans le PATH