<?php
ini_set('memory_limit','4084M');
include 'SpellCorrector.php';
include('simple_html_dom.php');
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$path ="/home/anchalkap/LATimesData/LATimesDownloadData/";

//Read the mapping file
$file = fopen('/home/anchalkap/LATimesData/mapLATimesDataFile.csv', 'r');
$csv = array();
while (($line = fgetcsv($file)) !== FALSE) {
  //$line is an array of the csv elements
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
// create a new solr service instance - host, port, and corename
// path (all defaults in this example)
$solr = new Apache_Solr_Service('localhost', 8983, '/solr/assignment1/');
 if( ! $solr->ping()) { 
            echo 'Solr service is not available'; 
        } 
     else{
     
     }

try
{
$queryterms = explode(" ",$query);
$original_query = $query;
$query = "";
$flag = 0;
$fg =isset($_REQUEST['f']) ? true : false;
if($fg == false){
foreach($queryterms as $term){
    $t = SpellCorrector::correct($term);
$t = SpellCorrector::correct($term);

    if(trim(strtolower($t)) != trim(strtolower($term))){
        $flag = 1;
    }
    $query = $query." ".$t;
}

$query = trim($query);
}else{
$query = $original_query;
}

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
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>LA Times Search Engine</title>

<script src="solr-php-client-master/jquery-3.2.1.min.js"></script>
<link rel="stylesheet" href="solr-php-client-master/jquery-ui-1.12.1.custom/jquery-ui.min.css">
<script src="solr-php-client-master/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

<script>
    $(function() {
        var URL_PREFIX = "http://localhost:8983/solr/assignment1/suggest?indent=on&q=";
        var URL_SUFFIX = "&wt=json";
        $("#q").autocomplete({
      source : function(request,response) {
        var input=$("#q").val().toLowerCase().split(" ").pop(-1);
        var URL=URL_PREFIX+input+URL_SUFFIX;
        $.ajax({
          url : URL,
          success : function(data) {
            var input=$("#q").val().toLowerCase().split(" ").pop(-1);
            var suggestions=data.suggest.suggest[input].suggestions;
            suggestions=$.map(suggestions,function(value,index){
              var prefix="";
              var query=$("#q").val();
              var queries=query.split(" ");
              if(queries.length>1) {
                var lastIndex=query.lastIndexOf(" ");
                prefix=query.substring(0,lastIndex+1).toLowerCase();
              }
              if (prefix == "" && is_stop_word(value.term)) {
                return null;
              }
               if(!/^[0-9a-zA-Z]+$/.test(value.term)) {
                return null;
              }
              return prefix+value.term;
            });
            response(suggestions.slice(0,5));
          },
          dataType: 'jsonp',
          jsonp: 'json.wrf'
        });  
      },
      minLength: 1 
    });
    });

function is_stop_word(stopword) {
  var regex=new RegExp("\\b"+stopword+"\\b","i");
  return stopWords.search(regex) < 0 ? false : true;
 }

</script>
</head>
<body style="text-align:center">
<div id = "search" top = "50px" left = "50px">
<form accept-charset="utf-8" method="get" >
<label for="q">LA Times Search Engine</label><br><br>
<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($original_query, ENT_QUOTES, 'utf-8'); ?>"/>
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
// display results
if ($results) {
if($flag == 1){ ?>
<p>Showing results for: <a href="http://localhost/ui3.php?rank=<?php echo $_REQUEST['optn']; ?>&f=true&q=<?php echo $query; ?>"><?php echo $query;?></a> </p>
<p>Search instead for: <a href="http://localhost/ui3.php?rank=<?php echo $_REQUEST['optn']; ?>&f=true&q=<?php echo $original_query; ?>"><?php echo $original_query;?></a> </p>
<?php }
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
// iterate result documents

foreach ($results->response->docs as $doc)
{ 
// iterate document fields / values
echo "<li>";


$title = "";
$url = "";
$id = "";
$descp = "";

foreach ($doc as $field => $value)
{ 

if($field == "id"){
$local_file = $value;
$id = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
$id = str_replace($path, "", $id);
}

if($field == "title"){
$title = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
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

$html = $descp.".".file_get_contents($local_file).".".$title;
$sentences = explode(".",$html);
$words = explode(" ", $query);
        $snippet="";
        $text="/";
        $start_delim="(?=.*?\b";
        $end_delim="\b)";
        foreach($words as $item){
            $text=$text.$start_delim.$item.$end_delim;
        }


        $text=$text."^.*$/i";
       
        foreach($sentences as $sentence){
            $sentence=strip_tags($sentence);
            if (preg_match($text, $sentence)>0){
            if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
                continue;
}
else{
$snippet = $snippet.$sentence;
            if(strlen($snippet)>156) break;
}
}
        }
if(strlen($snippet)<5){
foreach($sentences as $sentence){
            $sentence=strip_tags($sentence);
foreach($words as $word){
            if (preg_match($word, $sentence)>0){
            if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
                continue;
}
else{
$snippet = $snippet.$sentence;
            if(strlen($snippet)>156)break;
}
}
}
        }
}
       echo "...".$snippet."..."; 


echo "</li>";

}
?>
</ol>

</div>
<script>
var stopWords = "a,able,about,above,across,after,all,almost,also,am,among,can,an,and,any,are,as,at,be,because,been,but,by,cannot,could,dear,did,do,does,either,else,ever,every,for,from,get,got,had,has,have,he,her,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your,not";
</script>
</body> </html>
