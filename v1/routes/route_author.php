<?php
/**
 * Routes author manipulation - 'author' table concerned
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
 * Get all author
 * url - /authors
 * method - GET
 */
$app->get('/authors', 'authenticate', function() use ($app, $db, $logManager) {
    $authors = $db->entityManager->author();
    $authors_array = JSON::parseNotormObjectToArray($authors);

    global $user_connected;

    if(count($authors_array) > 0)
    {
        $data_authors = array();

        foreach ($authors as $author) array_push($data_authors, JSON::removeNode($author, "password_hash"));

        $logManager->setLog($user_connected, (string)$authors, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_authors);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$authors, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
 * Get one author by id
 * url - /authors/:id
 * method - GET
 */
$app->get('/authors/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $authors = $db->entityManager->author[$id];
    global $user_connected;

    if(count($authors) > 0)
    {
        $logManager->setLog($user_connected, (string)$authors, false);
        echoResponse(200, true, "L'author est retourné", $authors);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$authors, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});