<?php
/**
 * All functions utils needed
 */

include_once "set_headers.php";

/**
 * Add a new [key] => [value] pair after a specific Associative Key in an Assoc Array
 * @param $arr
 * @param $key
 * @param $val
 * @param $index
 * @return array
 */
function insterKeyValuePairInArray($arr, $key, $val, $index){
    $arrayEnd = array_splice($arr, $index);
    $arrayStart = array_splice($arr, 0, $index);
    return (array_merge($arrayStart, array($key=>$val), $arrayEnd ));
}

/**
 * Vérification les params nécessaires posté ou non
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    // Manipulation params de la demande PUT
    if ($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
        global $app;
        $request_params = json_decode($app->request()->getBody(), true);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        //Champ (s) requis sont manquants ou vides, echo erreur JSON et d'arrêter l'application
        global $app;
        echoResponse(400, false, 'Champ(s) requis ' . substr($error_fields, 0, -2) . ' est (sont) manquant(s) ou vide(s)', NULL);
        $app->stop();
    }
}

/**
 * Validation adresse e-mail
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        global $app;
        echoResponse(400, false, "Adresse e-mail n'est pas valide", NULL);
        $app->stop();
    }
}

/**
 * Faisant écho à la réponse JSON au client
 * @param String $status_code  Code de réponse HTTP
 * @param Int $response response Json
 */
function echoResponse($status_code, $state, $message, $data) {
    global $app;

    $app->status($status_code); // Code de réponse HTTP

    $app->contentType('application/json'); // la mise en réponse type de contenu en JSON

    $response = array();
    $response["result"]["state"] = $state;
    $response["result"]["message"] = $message;
    $response["records"] = $data;

    echo utf8_encode(json_encode($response));
}