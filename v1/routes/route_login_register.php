<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHODES sans authentification---------------------------------
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
 * Login Utilisateur
 * url - /login
 * method - POST
 * @params email, password
 */
$app->post('/login', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email', 'password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $author_query = $db->entityManager->author("email = ?", $email);
    $author = $author_query->fetch();

    $message_log = buildMessageLog($author, $app->request()->getResourceUri(), (string)$author_query, $app->request()->getIp()); //message log

    if( $author != FALSE ) //false si l'email de l'author n'est pas trouvé
    {
        if (PassHash::check_password($author['password_hash'], $password))
        {
            $user = JSON::removeNode($author, "password_hash"); //remove password_hash column from $user
            if($user["status"] == 0) //author activé
            {
                $log = sendMessageLog($message_log, false);
                echoResponseWithLog(200, true, "Connexion réussie", $author, $log); // Mot de passe utilisateur est correcte
                //echoResponse(200, true, "Connexion réussie", $author); // Mot de passe utilisateur est correcte
            }
            else
            {
                $log = sendMessageLog($message_log, true);
                echoResponseWithLog(200, true, "Connexion réussie", $author, $log); // Mot de passe utilisateur est correcte
                //echoResponse(200, false, "Votre compte a été suspendu", NULL); //author désactivé, status != 0
            }
        }
        else
        {
            $log = sendMessageLog($message_log, true);
            echoResponseWithLog(200, false, "Mot de passe incorrecte", NULL, $log); // erreur inconnue est survenue
        }
    }
    else
    {
        $log = sendMessageLog($message_log, true);
        echoResponseWithLog(200, false, "Echec de la connexion. identificateurs incorrectes", NULL, $log); // identificateurs de l'utilisateur sont erronés
    }
});

/**
 * Enregistrement de l'utilisateur
 * url - /register
 * methode - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('name', 'email', 'password')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name']; //$app->request->post('name');
    $email = $request_params['email']; //$app->request->post('email');
    $password = $request_params['password']; //$app->request->post('password');

    validateEmail($email); //valider adresse email

    $author_exist_query = $db->entityManager->author("email = ?", $email);
    $author_exist = $db->entityManager->author("email = ?", $email)->fetch();

    if($author_exist == FALSE) //email author doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_author = $db->entityManager->author()->insert($data);
        
        if($insert_author == FALSE)
        {
            $message_log = buildMessageLog(null, $app->request()->getResourceUri(), (string)$author_exist_query . " / " . buildSqlQueryInsert("author", $data), $app->request()->getIp()); //message log
            $log = sendMessageLog($message_log, true);

            //$logManager->setLog(null, (string)$author_exist_query . " / " . buildSqlQueryInsert("author", $data), true);

            echoResponseWithLog(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL, $log);
            //echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_author != FALSE || is_array($insert_author))
            {
                $message_log = buildMessageLog(null, $app->request()->getResourceUri(), (string)$author_exist_query . " / " . buildSqlQueryInsert("author", $data), $app->request()->getIp()); //message log
                $log = sendMessageLog($message_log, false);
                //$logManager->setLog(null, (string)$author_exist_query . " / " . buildSqlQueryInsert("author", $data), false);

                echoResponseWithLog(201, true, "Author inscrit avec succès", $insert_author, $log);
            }
        }
    }
    else
    {
        if($author_exist != FALSE || count($author_exist) > 1)
        {
            $message_log = buildMessageLog(null, $app->request()->getResourceUri(), (string)$author_exist_query, $app->request()->getIp()); //message log
            $log = sendMessageLog($message_log, false);
            //$logManager->setLog(null, (string)$author_exist_query, false);

            echoResponseWithLog(400, false, "Désolé, cet E-mail éxiste déja", NULL, $log);
            //echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});