<?php
/**
 * Routes user manipulation - 'user' table concerned
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
 * Get all user
 * url - /users
 * method - GET
 */
$app->get('/users', 'authenticate', function() use ($app, $db, $logManager) {
    $users = $db->entityManager->user();
    $users_array = JSON::parseNotormObjectToArray($users);

    global $user_connected;

    if(count($users_array) > 0)
    {
        $data_users = array();

        foreach ($users as $user) array_push($data_users, JSON::removeNode($user, "password_hash"));

        $logManager->setLog($user_connected, (string)$users, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_users);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$users, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
 * Get one user by id
 * url - /users/:id
 * method - GET
 */
$app->get('/users/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $users = $db->entityManager->user[$id];
    global $user_connected;

    if(count($users) > 0)
    {
        $logManager->setLog($user_connected, (string)$users, false);
        echoResponse(200, true, "L'user est retourné", $users);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$users, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});