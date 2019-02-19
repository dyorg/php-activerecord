<?php
/**
 * Created by PhpStorm.
 * User: Dyorg Washington G. Almeida
 * Date: 19/02/2019
 * Time: 17:46
 */

namespace ActiveRecord\Serialization;

/**
 * Array collection serializer.
 *
 * @package ActiveRecord
 */
class ArrayCollectionSerializer extends CollectionSerialization
{
    public function to_s()
    {
        return parent::to_array();
    }
}