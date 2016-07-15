<?php
/**
 * All functions utils needed
 */

include_once "set_headers.php";
require_once dirname(__DIR__) . '/Log.class.php';

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
        //if(!is_array($request_params[$field])) $strlen_values_fields = strlen(trim($request_params[$field])) <= 0;
        if (!isset($request_params[$field])) {
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

/**
 * Faisant écho à la réponse JSON au client et écriture dans le log
 * @param String $status_code  Code de réponse HTTP
 * @param Int $response response Json
 */
function echoResponseWithLog($status_code, $state, $message, $data, $log = array()) {
    global $app;

    $app->status($status_code); // Code de réponse HTTP

    $app->contentType('application/json'); // la mise en réponse type de contenu en JSON

    $response = array();
    $response["result"]["state"] = $state;
    $response["result"]["message"] = $message;
    $response["records"] = $data;

    echo utf8_encode(json_encode($response));

    if(count($log) > 0) exceptionLog($log); else return; //Write into log
}

/**
 * Build message log
 * @param $user
 * @param $ressourceUri
 * @param $sql_query
 * @return mixed
 */
function buildMessageLog($user, $ressourceUri, $sql_query, $ip_request)
{
    $message_log["user"] = is_null($user) ? NULL : array("id" => $user["id"], "name" => $user["name"]);
    $message_log["ressource"] = $ressourceUri;
    $message_log["sql query"] = $sql_query;
    $message_log["IP request"] = $ip_request;

    return $message_log;
}

/**
 * Send message log with state
 * @param $message_log
 * @param $state
 * @return mixed
 */
function sendMessageLog($message_log, $state, $method)
{
    $message = $message_log;
    $message = insterKeyValuePairInArray($message, "error", $state, 0);
    $message = insterKeyValuePairInArray($message, "method", $method, 1);
    return $message;
}

/**
 * Writes the log and returns the exception
 *
 * @param  string $message
 * @return string
 */
function exceptionLog($message)
{
    $log = new Log();
    $log->write(utf8_encode(json_encode($message))); #Write into log
}

/**
 * Build sql query insert need to get sql query on writing into log
 * @param $nameTable
 * @param $values
 * @return string
 */
function buildSqlQueryInsert($nameTable, $values)
{
    $values_sql = "";
    foreach ($values as $key => $value) {
        $values_sql .= $value . ",";
    }
    $values_sql = rtrim($values_sql, ","); //delete last ','

    return "INSERT INTO $nameTable VALUES($values_sql)";

}