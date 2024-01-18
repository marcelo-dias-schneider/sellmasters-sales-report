<?php

// allow access from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

$data_inizio = $_GET["data_inizio"];
$data_fine = $_GET["data_fine"];
$range_date = $_GET["range_date"];

# Arny
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

$response_arny = curl_exec($curl);
curl_close($curl);

$response_arny = json_decode($response_arny, true);

// find the position of nome MasinoGioielli and unset it
foreach ($response_arny as $key => $value) {
  if ($value['nome'] == 'MasinoGioielli') {
    unset($response_arny[$key]);
  }
}

# Marc
$curl = curl_init();

$options = array(
  CURLOPT_URL => 'https://scripts.sellmasters.dev/sellmasters_script_api_cms/ecommerce_resume/model.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{
    "action": "retrieve_resume_data",
    "date_range": "' . $range_date . '"
  }',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/json'
  ),
);

curl_setopt_array($curl, $options);

$response_marc = curl_exec($curl);

curl_close($curl);

$response_marc = json_decode($response_marc, true);

$response_marc_formated = [];
if ($response_marc == null) {
  $response_marc = [];
}
foreach ($response_marc as $value) {
  $response_marc_formated[] = [
    "quantita" => $value['quantity'],
    "totale_ordine" => $value['total'],
    "marketplace" => $value['ecommerce'],
    "nome" => $value['merchant']
  ];
}
// print_r($response_marc_formated);
// exit;
# Merge
$response = array_merge($response_arny, $response_marc_formated);

# format
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

// sum quantita and totale_ordine where nome and marketplace are the same
$response = array_reduce($response, function ($carry, $item) {
  $key = $item['nome'] . $item['marketplace'];
  if (isset($carry[$key])) {
    $carry[$key]['quantita'] += $item['quantita'];
    $carry[$key]['totale_ordine'] += $item['totale_ordine'];
  } else {
    $carry[$key] = $item;
  }
  return $carry;
}, []);

# return
echo json_encode($response);
