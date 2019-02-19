<?php
/**
 * Created by PhpStorm.
 * User: Dyorg Washington G. Almeida
 * Date: 19/02/2019
 * Time: 17:46
 */

namespace ActiveRecord\Serialization;

use ActiveRecord\ModelCollection;

/**
 * Base class for Model collection serializers.
 *
 * It's work with ModelCollection {@link ModelCollection}, that wraps objects Model to make mass serealization
 * All serializers support the same options of Serialization {@link Serialization}
 *
 * Example usage:
 *
 * <code>
 *
 * # populize object ModelCollection with only objects Model
 * $list_models = new ModelCollection(array());
 * $list_models[] = new Model();
 *
 * # collection serializer has supports to array, json and csv
 * # use all $options there are in class Serialization options
 * $list_models->to_array($options);
 * $list_models->to_json($options);
 * $list_models->to_csv($options);
 *
 * </code>
 *
 * @package ActiveRecord
 * @link http://www.phpactiverecord.org/guides/utilities#topic-serialization
 */
abstract class CollectionSerialization
{
    protected $collection;
    protected $options;

    /**
     * Constructs a {@link CollectionSerialization} object.
     *
     * @param ModelCollection $collection The collection of models to serialize
     * @param array &$options Options for serialization
     * @return CollectionSerialization
     */
    public function __construct(ModelCollection $collection, &$options)
    {
        $this->collection = $collection;
        $this->options = $options;
    }

    /**
     * Returns the models array.
     * @return array
     */
    public function to_array() {

        $array = array();

        foreach ($this->collection as $model) {
            $model_to_array = $model->to_array($this->options);

            foreach ($model_to_array as &$attribute) {
                if ($attribute instanceof ModelCollection) {
                    $attribute = $attribute->to_array($this->options);
                }
            }

            $array[] = $model_to_array;
        }

        return $array;
    }

    /**
     * Performs the serialization.
     * @return string
     */
    abstract public function to_s();

}