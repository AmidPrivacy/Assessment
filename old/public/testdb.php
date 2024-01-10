<?php

$servername = "172.23.41.104";
$username = "cc_user";
$password = "Asan1234";
$dbname = "cc_db";
 
try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $stmt = $conn->prepare("select * from calls_backup ");
  $stmt->execute();

  // set the resulting array to associative
  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  // foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
  //   echo $v;
  // }
} catch(PDOException $e) {
  echo "Error: " . $e->getMessage();
}


    echo "test";


$conn = null;
?>