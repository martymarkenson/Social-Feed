<?php

$servername = "";//enter server name
$username = "";//enter username
$password = "";//enter the password
$dbname="";//enter the name of your database


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//select highest ranked Link
$sql = "SELECT Link FROM $dbname ORDER by RANK DESC LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

//echo the embedable code from this Link
echo $row['Link'];

//if there are links in Database delete the highest ranking row

$sqlDelete="DELETE FROM $dbname ORDER by RANK DESC LIMIT 1";
	if ($conn->query($sqlDelete) === TRUE) {
   	 echo "";
	} else {
    echo "Error deleting record: " . $conn->error;
	}
	

//if database is empty repopulate it with 'social_feed.php'
if(empty($row)) {require('social_feed.php');//populate database
}
$conn->close();

?>