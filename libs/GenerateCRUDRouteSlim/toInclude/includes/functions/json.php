<?php

/**
 * Class JSON
 * Manipulation des retours data json
 */
class JSON
{
    /**
     * Remove specific key from json
     * @param $json_array
     * @param $key
     * @return mixed
     */
    static function removeNode($json_array, $key)
    {
        $json_string = json_encode($json_array);
        $json_array = json_decode($json_string, true);

        if(is_array($key))
            foreach ($key as $key_current)
                unset($json_array[$key_current]);
        else
            unset($json_array[$key]);

        return $json_array;
    }

    /**
     * Convert Notorm Object to array
     * @param $object
     * @return mixed
     */
    static function parseNotormObjectToArray($object)
    {
        return json_decode(json_encode($object), true);
    }
}

