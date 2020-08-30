<?php

//$rss = 'http://4pda.ru/feed/';
$rss = "https://gordonua.com/xml/rss_category/top.html";



$xmlDoc = new DOMDocument();
$xmlDoc->load( $rss );

$searchNode = $xmlDoc->getElementsByTagName( "item" );

foreach( $searchNode as $searchNode )
{

    $xmlDate = $searchNode->getElementsByTagName( "title" );
    $valueDate = $xmlDate->item(0)->nodeValue;



    echo  $valueDate."<hr>";
}


?>
