<?php

$audio_file = $_GET['audio'];



    $cURLConnection = curl_init(); 
    curl_setopt($cURLConnection, CURLOPT_URL, $audio_file );
    curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, 1); 
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($cURLConnection, CURLOPT_USERPWD, 'cmreport' . ":" . 'Asan1234');  
    curl_setopt($cURLConnection, CURLOPT_COOKIEFILE, 'cookie.txt'); 
    $phoneList = curl_exec($cURLConnection);
    curl_close($cURLConnection); 
    $jsonArrayResponse = json_decode($phoneList);

    echo '<audio controls>
    <source src="<?=$phoneList?>" type="audio/mp4">
    <source src="horse.mp3" type="audio/mpeg">
    Your browser does not support the audio tag.
  </audio>';
    // print $phoneList;


?>