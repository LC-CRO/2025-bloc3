<?php

function connectDB(){
  $host = '51.254.109.35:3306';
  $user = 'u6646_J2gDRG2QfM';
  $password = 'gT!SB2uZ@ND44+DXOwZOI3yA';
  $database = 's6646_bloc3';
  $conn = new mysqli($host, $user, $password, $database);

  if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
  }
  else{
    return $conn;
  }
}

