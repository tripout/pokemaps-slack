<?php
// https://raw.githubusercontent.com/rubenmak/PokemonGo-SlackBot/master/locales/pokemon.en.json
$json     = json_decode(file_get_contents("php://input"));
$pokemons = json_decode(file_get_contents("pokemon.en.json"), true);
$pokemonsimg = json_decode(file_get_contents("pokemon.num.json"), true);
$pokeimg  = $pokemonsimg[$json->{"message"}->{"pokemon_id"}];
$poke_id  = $json->{"message"}->{"pokemon_id"};
$poke = $pokemons[$poke_id];
$gone     = $json->{"message"}->{"disappear_time"};
$latitude = $json->{"message"}->{"latitude"};
$longitude = $json->{"message"}->{"longitude"};
$encounter_id = $json->{"message"}->{"encounter_id"};
$ignore   = [
    16, //Pidgey
    19, //Rattata
];
$url = "SLACK URL HERE";
$time = time();
$dbuser = "DB USER";
$dbpass = "DB PASS";

$db = "webhooks";
$dbtable = "webhooks";
$dbhost = "localhost";

$db = new mysqli($dbhost, $dbuser, $dbpass, $db);
$dbquery = "SELECT * FROM `$dbtable` WHERE `encounter_id` = '$encounter_id'";
$result = $db->query($dbquery);

if ($result->num_rows == 0) {
    $insert_query = "INSERT INTO `$dbtable` (`encounter_id`, `time`) VALUES ('".$encounter_id."', '".$gone."')";
    $result = $db->query($insert_query);
    send_webhook();
} 
else {
        $delete_query = "DELETE FROM `$dbtable` WHERE `time`<= $time";
        $result = $db->query($delete_query);
}

function send_webhook(){
    global $poke_id;
    global $poke;
    global $ignore;
    global $latitude;
    global $longitude;
    global $gone;
    global $pokeimg;
    global $url;
    
    if (!in_array($poke_id, $ignore)) {
        $data = "payload=" . json_encode(array(
            "channel"       =>  "#spawns",
            "icon_url"        => $pokeimg,
            "username"        => $poke,
            "text"          =>  "$poke ($poke_id) found, until ".date('g:i:s a', $gone)." <https://www.google.com/maps/dir/Current%2BLocation/".$latitude.",".$longitude."|Get Directions>",
        ));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}

?>
