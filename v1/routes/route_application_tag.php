<?php
/**
 * Routes application manipulation - 'application_tag' table concerned
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
 * Affecte application to tag
 * url - /applications
 * method - POST
 * @params id_application, id_tags
 */
$app->post('/application_tags/:id_application', 'authenticate', function($id_application) use ($app, $db, $logManager) {
    verifyRequiredParams(array('tags_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_tag = FALSE;

    foreach ($request_params["tags_id"] as $tag_app)
    {
        $data = array(
            "application_id" => $id_application,
            "tag_id" => $tag_app["id"]
        );

        $insert_application_app = $db->entityManager->application_tag()->insert($data);

        if($insert_application_app != FALSE || is_array($insert_application_app))
        {
            $inserted_tag = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("application_tag", $data), false); //application_tag insérée
        }
        else
        if($insert_application_app == FALSE)
        {
            $inserted_tag = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("application_tag", $data), true); //application_tag non insérée
        }
    }

    if($inserted_tag == TRUE)
        echoResponse(201, true, "Tags ajoutes", NULL);
    else if($inserted_tag == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});