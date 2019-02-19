<?php
/**
 * Created by PhpStorm.
 * User: Dyorg Washington G. Almeida
 * Date: 19/02/2019
 * Time: 17:33
 */

namespace ActiveRecord;

/**
 * Model collection
 *
 * It's wraps objects Model to make mass serealization
 *
 * Example usage:
 *
 * <code>
 * # populize object ModelCollection with only objects Model
 * $list_models = new ModelCollection(array());
 * $list_models[] = new YourModel();
 *
 * # collection serializer has supports to array, json and csv
 * # use all $options there are in class Serialization options {@link Serialization}
 * $list_models->to_array($options);
 * $list_models->to_json($options);
 * $list_models->to_csv($options);
 * </code>
 *
 * ModelCollection is a ArrayObject, it's not an array, so you can't use the built in array functions
 * you must use $list_models->is_empty() instead empty($list_models) or $list_models->array_keys() instead array_keys($list_models)
 *
 * <code>
 * # put new objeto model
 * $list_models[] = $yourObjectModel;
 *
 * # check if isset
 * isset($list_models[1]);
 *
 * # check is empty:
 * $list_models->is_empty();
 *
 * # get array keys:
 * $list_models->array_keys();
 *
 * # array map
 * $list_models->array_map();
 * </code>
 *
 * @package ActiveRecord
 * @see CollectionSerialization
 * @see Serialization
 * @see ArrayObject
 */
class ModelCollection extends \ArrayObject {

    /**
     * @see ArrayAccess::offsetSet()
     * @param $index
     * @param Model $model	Allow only Model objects
     */
    public function offsetSet($index, $model)
    {
        if (!$model instanceof Model)
            throw new  \RuntimeException('$model must be object Model instance');

        return parent::offsetSet($index, $model);
    }

    /**
     * magic method to call metods array_*
     * ArrayObject is not an array so you can't use the built in array functions
     * so use $modelCollection->array_keys() instead array_keys($modelCollection)
     */
    public function __call($func, $argv)
    {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_')
        {
            throw new \BadMethodCallException(__CLASS__.'->'.$func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }

    /**
     * is_empty method
     *
     * @return boolean
     */
    public function is_empty()
    {
        return $this->count() === 0;
    }

    /**
     * Returns an CSV representation with headers and rows of model list.
     * Can take optional delimiter and enclosure
     * (defaults are , and double quotes)
     *
     * Ex:
     * <code>
     * ActiveRecord\CsvSerializer::$delimiter=';';
     * ActiveRecord\CsvSerializer::$enclosure='';
     * $list_models->to_csv(array('only'=>array('name','level')));
     * returns:
     * name,level
     * John,1
     * Joe,5
     * Rachel,8
     *
     * $list_models->to_csv(array('only_header'=>true,'only'=>array('name','level')));
     * returns:
     * name,level
     *
     * $list_models->to_csv(array('only_rows'=>true,'only'=>array('name','level')));
     * returns:
     * John,1
     * Joe,5
     * Rachel,8
     *
     * </code>
     *
     * @see CollectionSerialization
     * @param array $options An array containing options for csv serialization (see {@link Serialization} for valid options)
     * @return string CSV representation of model list
     */
    public function to_csv(array $options=array())
    {
        return $this->_serialize('Csv', $options);
    }

    /**
     * Returns an Array representation of model list.
     *
     * @see CollectionSerialization
     * @param array $options An array containing options for json serialization (see {@link Serialization} for valid options)
     * @return array Array representation of model list
     */
    public function to_array(array $options=array())
    {
        return $this->_serialize('Array', $options);
    }

    /**
     * Returns a JSON representation of model list.
     *
     * @see CollectionSerialization
     * @param array $options An array containing options for json serialization (see {@link Serialization} for valid options)
     * @return string JSON representation of model list
     */
    public function to_json(array $options=array())
    {
        return $this->_serialize('Json', $options);
    }

    /**
     * Creates a serializer based on pre-defined to_serializer()
     *
     * An options array can take the following parameters:
     *
     * <ul>
     * <li><b>only:</b> a string or array of attributes to be included.</li>
     * <li><b>excluded:</b> a string or array of attributes to be excluded.</li>
     * <li><b>methods:</b> a string or array of methods to invoke. The method's name will be used as a key for the final attributes array
     * along with the method's returned value</li>
     * <li><b>include:</b> a string or array of associated models to include in the final serialized product.</li>
     * </ul>
     *
     * @param string $type Either Json, Csv or Array
     * @param array $options Options array for the serializer
     * @return mixed Serialized representation of model list
     */
    private function _serialize($type, $options)
    {
        $class = "ActiveRecord\\Serialization\\{$type}CollectionSerializer";
        $serializer = new $class($this, $options);
        return $serializer->to_s();
    }
}