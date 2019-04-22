<?php
/**
 * Created by PhpStorm.
 * User: Dyorg Washington G. Almeida
 * Date: 19/02/2019
 * Time: 17:46
 */

namespace ActiveRecord\Serialization;

use ActiveRecord\CsvSerializer;

/**
 * CSV collection serializer.
 *
 * CsvCollectionSerializer returns a list with headers and rows, and supports additional options to modify the result
 *
 * <ul>
 * <li><b>only_headers:</b> a string or array of attributes to be included.</li>
 * <li><b>only_rows:</b> a string or array of attributes to be excluded.</li>
 * </ul>
 *
 * Example usage:
 *
 * <code>
 * # return a complete list with headers and rows in csv format
 * # use all $options supported by class Serialization
 * # use existing CsvSerializer static attributes to modify delimiter and enclosure
 * CsvSerializer::$delimiter = ';';
 * CsvSerializer::$enclosure = '""';
 *
 * $list_models->to_csv($options)
 *
 * # return only headers in csv format
 * $list_models->to_csv(array('only_header' => true)
 *
 * # return only rows in csv format
 * $list_models->to_csv(array('only_rows' => true)
 *
 * # when regional symbol decimal is ',' instead '.' use convert_decimal option to convert
 * # it's works only when CsvSerializer::$delimiter is diferent than ','
 * $list_models->to_csv(array('convert_decimal' => array('money')))
 *
 * # rename headers to export
 * $list_models->to_csv(array('rename_header' => array('user_name' => 'User', 'user_email' => 'E-mail')))
 * </code>
 *
 * @package ActiveRecord
 */
class CsvCollectionSerializer extends CollectionSerialization
{
    public function to_s()
    {
        $models = parent::to_array();

        $stream = fopen('php://temp', 'w');

        if (isset($models[0]) && $this->get_options('only_rows') !== true) {

            $headers = array_keys($models[0]);

            // rename headers
            if ($rename_header = $this->get_options('rename_header')) {
                foreach($headers as &$attribute) {
                    if (array_key_exists($attribute, $rename_header)) {
                        $attribute = $rename_header[$attribute];
                    }
                }
            }

            fputcsv($stream, $headers, CsvSerializer::$delimiter, CsvSerializer::$enclosure);
        }

        if ($this->get_options('only_header') !== true) {

            $filters = $this->get_options('filters');

            foreach ($models as $model) {

                if ($filters) {
                    foreach ($model as $name => &$value) {
                        if (array_key_exists($name, $filters)) {
                            $value = call_user_func($filters[$name], $value);
                        }
                    }
                }

                fputcsv($stream, $model, CsvSerializer::$delimiter, CsvSerializer::$enclosure);

            }
        }

        rewind($stream);
        $buffer = trim(stream_get_contents($stream));
        fclose($stream);
        return $buffer;
    }

    private function get_options($key) {
        if (!isset($this->options[$key])) return null;
        return $this->options[$key];
    }
}