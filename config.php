<?php
require "vendor/autoload.php";

$client = new Google_Client();
$client->setApplicationName("Google Sheet Plugin");
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType("offline");
$client->setAuthConfig(wp_upload_dir()['path'] . '/credentials.json');

$service = new Google_Service_Sheets($client);


function highlight($spreadsheetId,$service,$start,$send)
{
    for($i=$start;$i<$send;$i++){
        $range = "E".$i;
        $value = [["Booked"]];
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];
        $requestBody = new Google_Service_Sheets_ValueRange(["values" => $value]);
        // $response = $service->spreadsheets_values->append($spreadsheetId, $range, $requestBody,$params);
        $response = $service->spreadsheets_values->update($spreadsheetId, $range, $requestBody,$params);
        // echo '<pre>', var_export($response, true), '</pre>', "\n";
    }
}   

function getdata($service,$spreadsheetID,$start,$end){
    $range = "A".$start.":Z".$end;
    
    
    $response = $service->spreadsheets_values->get($spreadsheetID, $range);
    $values = $response->getValues();

    if (empty($values)) {
        print "No data found.\n";
    }
    return $values;
}
