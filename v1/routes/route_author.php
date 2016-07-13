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

global $app;

/**
 * Get all author
 * url - /authors
 * method - GET
 */
$app->get('/authors', 'authenticate', function() use ($app) {
    $db = new DBManager();

    $authors = $db->entityManager->author();
    $authors_array = json_decode(json_encode($authors), true);

    if(count($authors_array) > 0)
    {
        $data_authors = array();
        foreach ($authors as $author) {
            array_push($data_authors, $author);
        }
        echoResponse(200, true, "Tous les auteurs retournés", $data_authors);
    }
    else
        echoResponse(400, true, "Une erreur est survenue.", NULL);

});

/**
 * Get one author by id
 * url - /authors/:id
 * method - GET
 */
$app->get('/authors/:id', 'authenticate', function($id) use ($app) {
    $db = new DBManager();

    $authors = $db->entityManager->author[$id];

    if(count($authors) > 0) echoResponse(200, true, "L'author est retourné", $authors);
    else echoResponse(400, true, "Une erreur est survenue.", NULL);
});