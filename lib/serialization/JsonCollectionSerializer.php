<?php
/**
 * Created by PhpStorm.
 * User: Dyorg Washington G. Almeida
 * Date: 19/02/2019
 * Time: 17:46
 */

namespace ActiveRecord\Serialization;

use ActiveRecord\ArraySerializer;
use ActiveRecord\JsonSerializer;

/**
 * JSON collection serializer.
 *
 * @package ActiveRecord
 */
class JsonCollectionSerializer extends ArrayCollectionSerializer
{
    public function to_s()
    {
        ArraySerializer::$include_root = JsonSerializer::$include_root;
        return json_encode(parent::to_s());
    }
}