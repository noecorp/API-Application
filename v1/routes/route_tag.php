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

global $app;

/**
 * Get all tag
 * url - /tags
 * method - GET
 */
$app->get('/tags', 'authenticate', function() use ($app) {
    $db = new DBManager();

    $tags = $db->entityManager->tag();
    $tags_array = json_decode(json_encode($tags), true);

    if(count($tags_array) > 0)
    {
        $data_tags = array();
        foreach ($tags as $tag) {
            array_push($data_tags, $tag);
        }
        echoResponse(200, true, "Tous les auteurs retournés", $data_tags);
    }
    else
        echoResponse(400, true, "Une erreur est survenue.", NULL);

});

/**
 * Get one tag by id
 * url - /tags/:id
 * method - GET
 */
$app->get('/tags/:id', 'authenticate', function($id) use ($app) {
    $db = new DBManager();

    $tag = $db->entityManager->tag[$id];

    if(count($tag) > 0) echoResponse(200, true, "L'author est retourné", $tag);
    else echoResponse(400, true, "Une erreur est survenue.", NULL);
});

/**
 * Create new tag
 * url - /tags/
 * method - POST
 * @params name
 */
$app->post('/tags', 'authenticate', function() use ($app) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    //global $author_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_tag = $request_params["name"]; //$app->request()->post('password');

    $db = new DBManager();
    $insert_tag = $db->entityManager->tag()->insert(array("name" => $name_tag));

    if($insert_tag == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du tag", NULL);
    else
        if($insert_tag != FALSE || is_array($insert_tag)) echoResponse(201, true, "Tag ajouté avec succès", $insert_tag);
});

/**
 * Update one tag
 * url - /tags/:id
 * method - PUT
 * @params name
 */
$app->put('/tags/:id', 'authenticate', function($id) use ($app) {
    verifyRequiredParams(array('name')); // vérifier les paramédtres requises
    //global $author_id;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_tag = $request_params["name"]; //$app->request()->post('password');

    $db = new DBManager();
    $tag = $db->entityManager->tag[$id];
    if($tag)
    {
        $update_tag = $tag->update(array("name" => $name_tag));

        if($update_tag == FALSE) echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du tag", NULL);
        else
            if($update_tag != FALSE || is_array($update_tag)) echoResponse(201, true, "Tag mis à jour avec succès", NULL);
    }
    else
        echoResponse(400, false, "Tag inexistant !!", NULL);

});

/**
 * Delete one tag
 * url - /tags/:id
 * method - DELETE
 * @params name
 */
$app->delete('/tags/:id', 'authenticate', function($id) use ($app) {
    $db = new DBManager();
    $tag = $db->entityManager->tag[$id];
    if($tag && $tag->delete()) echoResponse(201, true, "Tag id : $id supprimé avec succès", NULL);
    else echoResponse(400, false, "Erreur lors de la suppression de la tag ayant l'id $id !!", NULL);
});