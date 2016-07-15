<?php
/**
 * Routes tag manipulation - 'tag' table concerned
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
 * Get all tag
 * url - /tags
 * method - GET
 */
$app->get('/tags', 'authenticate', function() use ($app, $db, $logManager) {
    $tags = $db->entityManager->tag();
    $tags_array = JSON::parseNotormObjectToArray($tags);
    global $user_connected;

    if(count($tags_array) > 0)
    {
        $data_tags = array();
        foreach ($tags as $tag) array_push($data_tags, $tag);

        $logManager->setLog($user_connected, (string)$tags, false);
        echoResponse(200, true, "Tous les tags retournés", $data_tags);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$tags, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
 * Get one tag by id
 * url - /tags/:id
 * method - GET
 */
$app->get('/tags/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $tag = $db->entityManager->tag[$id];
    global $user_connected;

    if(count($tag) > 0)
    {
        $logManager->setLog($user_connected, (string)$tag, false);
        echoResponse(200, true, "L'author est retourné", $tag);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$tag, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
 * Create new tag
 * url - /tags/
 * method - POST
 * @params name
 */
$app->post('/tags', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_tag = $request_params["name"];

    $data = array(
        "name" => $name_tag
    );

    $insert_tag = $db->entityManager->tag()->insert($data);

    if($insert_tag == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("tag", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du tag", NULL);
    }
    else
        if($insert_tag != FALSE || is_array($insert_tag))
        {
            $logManager->setLog($user_connected, buildSqlQueryInsert("tag", $data), false);
            echoResponse(201, true, "Tag ajouté avec succès", $insert_tag);
        }
});

/**
 * Update one tag
 * url - /tags/:id
 * method - PUT
 * @params name
 */
$app->put('/tags/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_tag = $request_params["name"];

    $tag = $db->entityManager->tag[$id];
    if($tag)
    {
        $update_tag = $tag->update(array("name" => $name_tag));

        if($update_tag == FALSE)
        {
            $logManager->setLog($user_connected, (string)$tag, true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du tag", NULL);
        }
        else
            if($update_tag != FALSE || is_array($update_tag))
            {
                $logManager->setLog($user_connected, (string)$tag, false);
                echoResponse(201, true, "Tag mis à jour avec succès", NULL);
            }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$tag, true);
        echoResponse(400, false, "Tag inexistant !!", NULL);
    }

});

/**
 * Delete one tag
 * url - /tags/:id
 * method - DELETE
 * @params name
 */
$app->delete('/tags/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $tag = $db->entityManager->tag[$id];
    global $user_connected;

    if($db->entityManager->application_tag("tag_id", $id)->delete())
        if($tag && $tag->delete())
        {
            $logManager->setLog($user_connected, (string)$tag, false);
            echoResponse(200, true, "Tag id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$tag, true);
            echoResponse(200, false, "Tag id : $id pas supprimé. Erreur !!", NULL);
        }
    else
    {
        $logManager->setLog($user_connected, (string)$tag, true);
        echoResponse(400, false, "Erreur lors de la suppression de la tag ayant l'id $id : tag inexistant !!", NULL);
    }
});