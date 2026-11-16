<?php

$uri=getenv("MONGODB_URI");

if(!$uri){
    throw new RuntimeException("neno non configuraches a url");
}

$manager = new MongoDB\Driver\Manager($uri);
$usuarios = "lume_db.users";
$produtos = "lume_db.products"

?>