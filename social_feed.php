<!DOCTYPE HTML> 
<html>
<head>
</head>

<?php
//this function returns the values called on the instagram api
function callInstagram($url)
   {
   $ch = curl_init();
    curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 2
    ));

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    }

//Enter the information for your database
$servername = "";
$username = "";
$password = "";
$dbname="";

//this array will hold all the posts in the order in which they were posted
$masterArray= array();

//This is an open source twitter wrapper used to get the most recent tweets for a hastag
require_once("twitter-api-php/TwitterAPIExchange.php");

//populate this with your twitter access tokens
$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);

//the key is the time created and the value is the value returned from twitter for oembed
$twitter_array = array();

//url to search recent tweets
$url_twitterHashtag = 'https://api.twitter.com/1.1/search/tweets.json';

//choose twitter hastag you want to display
$getfield ='q=%23' . 'hashtag';

$requestMethod = 'GET';
$twitter= new TwitterAPIExchange($settings);

//returns the latest twitter posts from given hastag
$responseJson = $twitter->setGetfield($getfield)
             ->buildOauth($url_twitterHashtag, $requestMethod)
             ->performRequest();  


//Reformates the responses 
$response = json_decode($responseJson,true); 

//this loop will populate the master array with latest twitter posts from given hashtag in an embedable format
foreach ($response['statuses'] as $arrSearchResult) {  
	$twitter_time=strtotime($arrSearchResult['created_at']);
	$url_twitterOembed= Json_decode(file_get_contents("https://api.twitter.com/1/statuses/oembed.json?url=https://twitter.com/Interior/status/" . $arrSearchResult['id']),true)['html'];
	
    //adds url and time posted from twitter api into the master array
    $masterArray[$twitter_time]=$url_twitterOembed;
	}

//choose the instagram hastag you want to display
$tag = 'hashtag';

//enter your client_id from instagram
$client_id = "";

//url for instagram api
$url_instagram = 'https://api.instagram.com/v1/tags/'.$tag.'/media/recent?client_id='.$client_id;

$inst_stream = callInstagram($url_instagram);
$results = json_decode($inst_stream, true);

//populate the master array with latest instagram posts from given hashtag in an embedable format
    foreach($results['data'] as $item){
       $conn = new mysqli($servername, $username, $password, $dbname);
       $insta_time = $item['created_time'];
       $insta_url = Json_decode(file_get_contents("http://api.instagram.com/oembed?url=" . $item['link']),true)['html'];
       $masterArray[$insta_time]=$insta_url;
     }

//sorts the masterArray by time
krsort($masterArray);

//the rank in the database to record when it was entered into the database
$x=1;

//adds values from masterArray into database
foreach($masterArray as $item){
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
	} 
	$time = array_search($item,$masterArray);
	$sql = "INSERT INTO $dbname (link,rank,time)
	VALUES ('$item',$x,'$time')";
	if ($conn->query($sql) === TRUE) {
    echo " ";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
	
}
$x=$x+1; 
}
?>
</body>
</html>