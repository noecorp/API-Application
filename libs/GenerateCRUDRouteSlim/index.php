<?php

require_once('template/class/dao/sql/Connection.class.php');
require_once('template/class/dao/sql/ConnectionFactory.class.php');
require_once('template/class/dao/sql/ConnectionProperty.class.php');
require_once('template/class/dao/sql/QueryExecutor.class.php');
require_once('template/class/dao/sql/Transaction.class.php');
require_once('template/class/dao/sql/SqlQuery.class.php');
require_once "template/class/Template.php";

/**
 * Run generate routes
 * @throws Exception
 */
function generate()
{
    init();
    $sql = 'SHOW TABLES';
    $ret = QueryExecutor::execute(new SqlQuery($sql));
    generateAllRoutesFiles($ret);
}

/**
 * Init function, create generated folder
 */
function init()
{
    @mkdir("generated");
    @mkdir("generated/routes");
    @mkdir("../../v1/routes_automatic_generated");
}

/**
 * Test if table contains primary key
 * @param $row
 * @return bool
 */
function doesTableContainPK($row)
{
    $row = getFields($row[0]);
    for($j=0; $j<count($row); $j++)
    {
        if($row[$j][3]=='PRI') return true;
    }
    return false;
}

/**
 * Test if column is type number or like
 * @param $columnType
 * @return bool
 */
function isColumnTypeNumber($columnType)
{
    if(strtolower(substr($columnType,0,3)) == 'int' || strtolower(substr($columnType,0,7)) == 'tinyint')
    {
        return true;
    }
    return false;
}

/**
 * Get all fields in table
 * @param $table
 * @return wynik
 * @throws Exception
 */
function getFields($table)
{
    $sql = 'DESC '.$table;
    return QueryExecutor::execute(new SqlQuery($sql));
}

/**
 * Get all params need to post
 * @param $allFields
 * @return string
 */
function getFieldsParams($tableName)
{
    $allFields = getFields($tableName);
    $champs = "";
    foreach ($allFields as $champ) {
        if(($champ["Key"] == "PRI" && $champ["Extra"] == "auto_increment") || $champ["Key"] == "PRI" || $champ["Default"] != NULL || ($champ["Key"] == "MUL" && isColumnTypeNumber($champ["Type"]) == TRUE)) continue; //si c'est un clé primaire ou auto_increment ou un champ de type number ou un index
        else
            $champs .= $champ["Field"] . "','";
    }
    $champs = rtrim($champs, "'");
    $champs = rtrim($champs, ",");
    $champs = rtrim($champs, "'");

    return $champs;
}

/**
 * Enter description here...
 *
 * @param $ret
 * @return null
 */
function generateAllRoutesFiles($ret)
{
    error_reporting(E_ALL ^ E_DEPRECATED);

    $list_user_tables = array("author", "users", "user", "fournisseurs", "fournisseur"); //ajouter ici la liste des noms des tables qui peut se connecter à l'application

    $list_table_affected_by_association = array(
        "application" => "tag"
    );

    $fileCreated = "<h3>Listes des fichiers de routes crées à ajouter dans index.php du répértoire v1 : </h3>";

    for($i=0;$i<count($ret);$i++)
    {
        if(!doesTableContainPK($ret[$i])) continue;

        $tableName = $ret[$i][0];

        if(in_array($tableName, $list_user_tables)) //si c'est un table d'utilisateur de l'application
        {
            $template = new Template('template/tpl/route_[users].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params', getFieldsParams($tableName));
            $template->write('generated/routes/route_'.$tableName.'.php');

            //login-register
            $template = new Template('template/tpl/route_[login_register].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params_login', "email','password");
            $template->set('required_params_register', "name','email','password");
            $template->write('generated/routes/route_login_register_'.$tableName.'.php');

            $fileCreated .= "require_once 'routes_automatic_generated/route_$tableName.php'; <br>";
            $fileCreated .= "require_once 'routes_automatic_generated/route_login_register_$tableName.php'; <br>";

            copy('generated/routes/route_'.$tableName.'.php', '../../v1/routes_automatic_generated/route_'.$tableName.'.php');
            copy('generated/routes/route_login_register_'.$tableName.'.php', '../../v1/routes_automatic_generated/route_login_register_'.$tableName.'.php');
        }
        else
        if(array_key_exists($tableName, $list_table_affected_by_association)) //si la table est en relation avec un autre
        {
            $template = new Template('template/tpl/route_[table_affected_by_association].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params', getFieldsParams($tableName));
            $template->set('table_name_affected', $list_table_affected_by_association[$tableName]);
            $template->write('generated/routes/route_'.$tableName.'.php');

            $fileCreated .= "require_once 'routes_automatic_generated/route_$tableName.php'; <br>";

            copy('generated/routes/route_'.$tableName.'.php', '../../v1/routes_automatic_generated/route_'.$tableName.'.php');
        }
        else
        if(strpos($tableName, "_") !== FALSE) //si c'est une table d'association
        {
            $template = new Template('template/tpl/route_[tablename_association].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params', getFieldsParams($tableName));
            $template->set('table_name_first_part', explode("_",$tableName)[0]);
            $template->set('table_name_second_part', explode("_",$tableName)[1]);
            $template->write('generated/routes/route_'.$tableName.'.php');

            $fileCreated .= "require_once 'routes_automatic_generated/route_$tableName.php'; <br>";

            copy('generated/routes/route_'.$tableName.'.php', '../../v1/routes_automatic_generated/route_'.$tableName.'.php');
        }
        else
        if(strpos($tableName, "_") === FALSE) //si c'est une table simple à màj
        {
            $template = new Template('template/tpl/route_[tablename].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params', getFieldsParams($tableName));
            $template->write('generated/routes/route_'.$tableName.'.php');

            $fileCreated .= "require_once 'routes_automatic_generated/route_$tableName.php'; <br>";

            copy('generated/routes/route_'.$tableName.'.php', '../../v1/routes_automatic_generated/route_'.$tableName.'.php');
        }
    }

    //route_login_register_simple
    copy('generated/routes/route_login_register_author.php', 'generated/routes/route_login_register.php');
    copy('generated/routes/route_login_register_author.php', '../../v1/routes_automatic_generated/route_login_register.php');

    $fileCreated .= "require_once 'routes_automatic_generated/route_login_register.php'; <br>";

    echo $fileCreated;
}

generate(); //générer les fichiers routes