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
require_once dirname(__DIR__)  . '/includes/Log.class.php';

global $app;
$db = new DBManager();
$logManager = new Log();

/**
 * Get all applications
 * url - /applications
 * method - GET
 */
$app->get('/applications', 'authenticate', function() use ($app, $db, $logManager) {
    global $user_id, $user_connected;

    $applications = $db->entityManager->application("author_id", $user_id);
    $applications_array = JSON::parseNotormObjectToArray($applications);

    if(count($applications_array) > 0)
    {
        $data_applications = array();
        foreach ($applications as $application)
        {
            $data_tag = array();
            foreach ($application->application_tag() as $application_tag)
            {
                array_push($data_tag, array("id" => $application_tag->tag["id"], "name" => $application_tag->tag["name"]));
            }
            $application = JSON::parseNotormObjectToArray($application); //parse application to array
            $application["tags"] = $data_tag; //add tags from array

            array_push($data_applications, $application);
        }
        $logManager->setLog($user_connected, (string)$applications, false);
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
$app->get('/applications/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    global $user_connected;
    $application = $db->entityManager->application[$id];

    if(count($application) > 0)
    {
        $logManager->setLog($user_connected, (string)$application, false);
        echoResponse(200, true, "L'author est retourné", $application);
    }
    else echoResponse(400, true, "Une erreur est survenue.", NULL);
});

/**
 * Create new application
 * url - /applications
 * method - POST
 * @params title, web, slogan
 */
$app->post('/applications', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('title', 'web', 'slogan')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insterKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insterKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $insert_application = $db->entityManager->application()->insert($request_params);

    if($insert_application == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("application", $request_params), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du application", NULL);
    }
    else
        if($insert_application != FALSE || is_array($insert_application))
        {
            $logManager->setLog($user_connected, buildSqlQueryInsert("application", $request_params), false);
            echoResponse(201, true, "Application ajoutée avec succès", $insert_application);
        }
});

/**
 * Update one application
 * url - /applications/:id
 * method - PUT
 * @params title, web, slogan
 */
$app->put('/applications/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('title', 'web', 'slogan')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insterKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insterKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $application = $db->entityManager->application[$id];
    if($application)
    {
        $update_application = $application->update($request_params);

        if($update_application == FALSE)
        {
            $logManager->setLog($user_connected, (string)$application, true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du application", NULL);
        }
        else
            if($update_application != FALSE || is_array($update_application))
            {
                $logManager->setLog($user_connected, (string)$application, false);
                echoResponse(200, true, "Tag mis à jour avec succès. Id : $id", NULL);
            }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$application, true);
        echoResponse(400, false, "Tag inexistant !!", NULL);
    }

});

/**
 * Delete an application, need to delete from association table first
 * url - /applications/:id
 * method - DELETE
 * @params name
 */
$app->delete('/applications/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    global $user_connected;
    $application = $db->entityManager->application[$id];

    $application_tag = $db->entityManager->application_tag("application_id", $id)->fetch();

    if($application_tag != FALSE)
    {
        if($db->entityManager->application_tag("application_id", $id)->delete())
            if($application && $application->delete())
            {
                $logManager->setLog($user_connected, (string)$application, false);
                echoResponse(200, true, "Application id : $id supprimée avec succès", NULL);
            }
            else
            {
                $logManager->setLog($user_connected, (string)$application, true);
                echoResponse(200, false, "Application id : $id n'a pa pu être supprimée", NULL);
            }
        else
        {
            $logManager->setLog($user_connected, (string)$application, true);
            echoResponse(400, false, "Erreur lors de la suppression de la application ayant l'id $id !!", NULL);
        }
    }
    else if($application_tag == FALSE)
    {
        if($application && $application->delete())
        {
            $logManager->setLog($user_connected, (string)$application, false);
            echoResponse(200, true, "Application id : $id supprimée avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$application, true);
            echoResponse(200, false, "Application id : $id n'a pa pu être supprimée", NULL);
        }
    }

});