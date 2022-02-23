<?php

$data_inizio = $_GET["data_inizio"];
$data_fine = $_GET["data_fine"];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://51.91.97.200/sellmaster/reports/marc_json.php?data_inizio=$data_inizio&data_fine=$data_fine",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
curl_close($curl);

$response = json_decode($response, true);

foreach ($response as $key => $value) {
  $nome = $value['nome'];
  $nome = str_replace('_', ' ', $nome);
  $nome = strtolower($nome);
  $nome = ucfirst($nome);
  $response[$key]['nome'] = $nome;

  $marketplace = $value['marketplace'];
  $marketplace = strtolower($marketplace);
  $marketplace = ucwords($marketplace);
  $marketplace = explode('.', $marketplace)[0];
  $marketplace = explode('_', $marketplace)[0];
  $response[$key]['marketplace'] = $marketplace;

  $response[$key]['quantita'] = (float) $value['quantita'];
  $response[$key]['totale_ordine'] = (float) $value['totale_ordine'];
}

$response = json_encode($response);

echo $response;