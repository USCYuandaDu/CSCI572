<?php
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$path ="/home/anchalkap/LATimesData/LATimesDownloadData/";

$file = fopen('/home/anchalkap/LATimesData/mapLATimesDataFile.csv', 'r');
$csv = array();
while (($line = fgetcsv($file)) !== FALSE) {
$csv[$line[0]] = $line[1]; 
}
fclose($file);

$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false; 
$results = false;

if($query){
$additionalParameters = array(
 'sort' => 'pageRankFile desc'
);

require_once('solr-php-client-master/Apache/Solr/Service.php');

$solr = new Apache_Solr_Service('localhost', 8983, '/solr/assignment1/');
 if( ! $solr->ping()) { 
            echo 'Solr service is not available'; 
        } 
     else{
     
     }

try
{
if (isset($_GET["optn"]) && $_GET["optn"]=="rank"){
$results = $solr->search($query, 0, $limit,$additionalParameters);
}
else{
$results = $solr->search($query, 0, $limit);
}

}
catch (Exception $e)
{
die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
} 
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>LA Times Search Engine</title>

</head>
<body style="text-align:center">
<div id = "search" top = "50px" left = "50px">
<form accept-charset="utf-8" method="get" >
<label for="q">LA Times Search Engine</label><br><br>
<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
</br>
</br>
<input type="radio" name="optn" checked <?php if (isset($_GET["optn"]) && $_GET["optn"]=="default") echo "checked"?> value ="default"> Solr Lucene
 <input type="radio" name="optn" <?php if (isset($_GET["optn"]) && $_GET["optn"]=="rank") echo "checked" ?> value="rank"> Page Rank 
</br>
</br>
<input type="submit"/> 
</br>
</br>

</form>

</div>
<div style="text-align:left">
<?php

if ($results) {
$total = (int) $results->response->numFound; 
$start = min(1, $total);
$end = min($limit, $total);
}
?>
<?php

if ($results) {
 echo "<div>Results {$start} - {$end} of {$total}:</div>";
}
?>

<ol> 
<?php

foreach ($results->response->docs as $doc)
{ 
echo "<li>";

$title = "";
$url = "";
$id = "";
$descp = "";

foreach ($doc as $field => $value)
{ 

if($field == "title"){
$title = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');

}

if($field == "id"){
$id = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
$id = str_replace($path, "", $id);
}

if($field == "description"){
$descp = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
}
}
if($id != ""){
$url = $csv[$id];
}
echo "<a  target= '_blank'  href='{$url}'><b>".$title."</b></a></br></br>";
echo "<a  target= '_blank' href='{$url}'>".$url."</a></td></br>";
echo $descp."</br> </br>";

echo "</li>";

}
?>
</ol>

</div>
</body> </html>
