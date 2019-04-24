PostgresToGwern
=================

Exemples :
==========

$pTG = new PostgresToGwern("localhost", "postgres", "root", "downloadShpZip");

$pTG->query("test1", "gwern1", "\"SELECT * FROM ".'\"zh2-polygon\"'." WHERE id='idtutu'\"");

Requirements :
==============

Php 7.1
postgresql 11
pgsql2shp dans le PATH