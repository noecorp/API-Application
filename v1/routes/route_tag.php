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
        echoResponse(200, true, "Tous les tags retournes", $data_tags);
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
        echoResponse(200, true, "tag retourne(e)", $tag);
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
    verifyRequiredParams(array('name')); // verifier les paramedtres requises
    global $user_connected;

    //recuperer les valeurs POST
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
        echoResponse(201, true, "tag ajoute(e) avec succes", $insert_tag);
    }
});

/**
* Update one tag
* url - /tags/:id
* method - PUT
* @params name
*/
$app->put('/tags/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('name')); // verifier les parametres requises
    global $user_connected;

    //recuperer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_tag = $request_params["name"];

    $tag = $db->entityManager->tag[$id];
    if($tag)
    {
        $testSameData = isSameData($tag, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$tag, false);
            echoResponse(200, true, "Tag mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_tag = $tag->update(array("name" => $name_tag));
            if($update_tag == FALSE)
            {
                $logManager->setLog($user_connected, (string)$tag, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise a jour du tag", NULL);
            }
            else
                if($update_tag != FALSE || is_array($update_tag))
                {
                    $logManager->setLog($user_connected, (string)$tag, false);
                    echoResponse(201, true, "tag mis a jour avec succes", NULL);
                }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$tag, true);
        echoResponse(400, false, "tag inexistant !!", NULL);
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
    {
        if($tag && $tag->delete())
        {
            $logManager->setLog($user_connected, (string)$tag, false);
            echoResponse(200, true, "tag id : $id supprime avec succes", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$tag, true);
            echoResponse(200, false, "tag id : $id pas supprime. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$tag, true);
        echoResponse(400, false, "Erreur lors de la suppression de la tag ayant l'id $id : tag inexistant !!", NULL);
    }
});