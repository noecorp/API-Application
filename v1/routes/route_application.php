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
 * Get all application
 * url - /application
 * method - GET
 */
$app->get('/applications', 'authenticate', function() use ($app) {
    $db = new DBManager();

    $applications = $db->entityManager->application();
    $applications_array = json_decode(json_encode($applications), true);

    if(count($applications_array) > 0)
    {
        $data_applications = array();
        foreach ($applications as $application) {
            array_push($data_application, $application);
        }
        echoResponse(200, true, "Tous les applications retournés", $data_applications);
    }
    else
        echoResponse(400, true, "Une erreur est survenue.", NULL);

});

/**
 * Get one application by id
 * url - /application/:id
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
 * url - /application/
 * method - POST
 * @params name
 */
$app->post('/applications', 'authenticate', function() use ($app) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    //global $author_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_application = $request_params["name"]; //$app->request()->post('password');

    $db = new DBManager();
    $insert_application = $db->entityManager->application()->insert(array("name" => $name_application));

    if($insert_application == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du application", NULL);
    else
        if($insert_application != FALSE || is_array($insert_application)) echoResponse(201, true, "Tag ajouté avec succès", $insert_application);
});

/**
 * Update one application
 * url - /applications/:id
 * method - PUT
 * @params name
 */
$app->put('/applications/:id', 'authenticate', function($id) use ($app) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    //global $author_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_application = $request_params["name"]; //$app->request()->post('password');

    $db = new DBManager();
    $application = $db->entityManager->application[$id];
    if($application)
    {
        $update_application = $application->update(array("name" => $name_application));

        if($update_application == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du application", NULL);
        else
            if($update_application != FALSE || is_array($update_application)) echoResponse(201, true, "Tag mis à jour avec succès", NULL);
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
    if($application && $application->delete()) echoResponse(201, true, "Tag id : $id supprimé avec succès", NULL);
    else echoResponse(400, false, "Erreur lors de la suppression de la application ayant l'id $id !!", NULL);
});