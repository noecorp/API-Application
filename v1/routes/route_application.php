<?php
/**
 * Routes application manipulation - 'application' table concerned
 * ----------- METHODES avec authentification ----------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';

global $app;

/**
 * Get all applications
 * url - /applications
 * method - GET
 */
$app->get('/applications', 'authenticate', function() use ($app) {
    $db = new DBManager();

    $applications = $db->entityManager->application();
    $applications_array = JSON::parseNotormObjectToArray($applications);

    if(count($applications_array) > 0)
    {
        $data_applications = array();
        foreach ($applications as $application) array_push($data_applications, $application);

        echoResponse(200, true, "Tous les applications retournés", $data_applications);
    }
    else
        echoResponse(400, true, "Une erreur est survenue.", NULL);

});

/**
 * Get one application by id
 * url - /applications/:id
 * method - GET
 */
$app->get('/applications/:id', 'authenticate', function($id) use ($app) {
    $db = new DBManager();

    $application = $db->entityManager->application[$id];

    if(count($application) > 0) echoResponse(200, true, "L'author est retourné", $application);
    else echoResponse(400, true, "Une erreur est survenue.", NULL);
});

/**
 * Create new application
 * url - /applications
 * method - POST
 * @params title, web, slogan
 */
$app->post('/applications', 'authenticate', function() use ($app) {
    verifyRequiredParams(array('title', 'web', 'slogan')); // vérifier les paramétres requises
    global $user_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insterKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insterKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $db = new DBManager();
    $insert_application = $db->entityManager->application()->insert($request_params);

    if($insert_application == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du application", NULL);
    else
        if($insert_application != FALSE || is_array($insert_application)) echoResponse(201, true, "Application ajoutée avec succès", $insert_application);
});

/**
 * Update one application
 * url - /applications/:id
 * method - PUT
 * @params title, web, slogan
 */
$app->put('/applications/:id', 'authenticate', function($id) use ($app) {
    verifyRequiredParams(array('title', 'web', 'slogan')); // vérifier les paramétres requises
    global $user_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insterKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insterKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $db = new DBManager();
    $application = $db->entityManager->application[$id];
    if($application)
    {
        $update_application = $application->update($request_params);

        if($update_application == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du application", NULL);
        else
            if($update_application != FALSE || is_array($update_application)) echoResponse(200, true, "Tag mis à jour avec succès. Id : $id", NULL);
    }
    else
        echoResponse(400, false, "Tag inexistant !!", NULL);

});

/**
 * Delete one application
 * url - /applications/:id
 * method - DELETE
 * @params name
 */
$app->delete('/applications/:id', 'authenticate', function($id) use ($app) {
    $db = new DBManager();
    $application = $db->entityManager->application[$id];

    if($db->entityManager->application_tag("application_id", $id)->delete())
        if($application && $application->delete())
            echoResponse(201, true, "Application id : $id supprimée avec succès", NULL);
    else
        echoResponse(400, false, "Erreur lors de la suppression de la application ayant l'id $id !!", NULL);
});