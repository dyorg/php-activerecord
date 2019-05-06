<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;

/**
 * Adapter for Postgres (not completed yet)
 * 
 * @package ActiveRecord
 */
class PgsqlAdapter extends Connection
{
	static $QUOTE_CHARACTER = '"';
	static $DEFAULT_PORT = 5432;

    public $schema;

    public function __construct($info)
    {
        if (!empty($info->schema)) {
            $this->schema = $info->schema;
        }
        parent::__construct($info);
    }

    public function query($sql, &$values=array())
    {
        if (!empty($this->schema)) {
            $sql = preg_replace('/(from|join|update)\s+(\w+[^\w\(\.])/i', "$1 {$this->schema}.$2", $sql);
        }
        return parent::query($sql, $values);
    }

	public function supports_sequences()
	{
		return true;
	}

    public function get_sequence_name($table, $column_name)
    {
        $sequence_name = "{$table}_{$column_name}_seq";

        if (!empty($this->schema)) {
            $sequence_name = $this->schema . '.' . $sequence_name;
        }

        return $sequence_name;
    }

	public function next_sequence_value($sequence_name)
	{
		return "nextval('" . str_replace("'","\\'",$sequence_name) . "')";
	}

	public function limit($sql, $offset, $limit)
	{
		return $sql . ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
	}

	public function query_column_info($table)
	{
		$sql = "SELECT
      		a.attname AS field,
      		a.attlen,
      		REPLACE(pg_catalog.format_type(a.atttypid, a.atttypmod), 'character varying', 'varchar') AS type,
      		a.attnotnull AS not_nullable,
      		(SELECT 't'
        	FROM pg_index
        	WHERE c.oid = pg_index.indrelid
        	AND a.attnum = ANY (pg_index.indkey)
        	AND pg_index.indisprimary = 't'
      		) IS NOT NULL AS pk,      
      		REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE((SELECT pg_attrdef.adsrc
        	FROM pg_attrdef
        	WHERE c.oid = pg_attrdef.adrelid
        	AND pg_attrdef.adnum=a.attnum
      		),'::[a-z_ ]+',''),'''$',''),'^''','') AS default
	  		FROM pg_attribute a
			JOIN pg_class c ON c.oid = a.attrelid
			JOIN pg_type t on t.oid = a.atttypid
			JOIN pg_namespace n ON n.oid = c.relnamespace
			WHERE n.nspname = ? 
			AND c.relname = ?
			AND a.attnum > 0
			ORDER BY a.attnum
			";

        $values = explode('.', $table);
		return parent::query($sql,$values);
	}

	public function query_for_tables()
	{
		return parent::query("SELECT tablename FROM pg_tables WHERE schemaname NOT IN('information_schema','pg_catalog')");
	}

	public function create_column(&$column)
	{
		$c = new Column();
		$c->inflected_name	= Inflector::instance()->variablize($column['field']);
		$c->name			= $column['field'];
		$c->nullable		= ($column['not_nullable'] ? false : true);
		$c->pk				= ($column['pk'] ? true : false);
		$c->auto_increment	= false;

		if (substr($column['type'],0,9) == 'timestamp')
		{
			$c->raw_type = 'datetime';
			$c->length = 19;
		}
		elseif ($column['type'] == 'date')
		{
			$c->raw_type = 'date';
			$c->length = 10;
		}
		else
		{
			preg_match('/^([A-Za-z0-9_]+)(\(([0-9]+(,[0-9]+)?)\))?/',$column['type'],$matches);

			$c->raw_type = (count($matches) > 0 ? $matches[1] : $column['type']);
			$c->length = count($matches) >= 4 ? intval($matches[3]) : intval($column['attlen']);

			if ($c->length < 0)
				$c->length = null;
		}

		$c->map_raw_type();

		if ($column['default'])
		{
			preg_match("/^nextval\('(.*)'\)$/",$column['default'],$matches);

			if (count($matches) == 2)
				$c->sequence = $matches[1];
			else
				$c->default = $c->cast($column['default'],$this);
		}
		return $c;
	}

	public function set_encoding($charset)
	{
		parent::query("SET NAMES '$charset'");
	}

	public function native_database_types()
	{
		return array(
			'primary_key' => 'serial primary key',
			'string' => array('name' => 'character varying', 'length' => 255),
			'text' => array('name' => 'text'),
			'integer' => array('name' => 'integer'),
			'float' => array('name' => 'float'),
			'datetime' => array('name' => 'datetime'),
			'timestamp' => array('name' => 'timestamp'),
			'time' => array('name' => 'time'),
			'date' => array('name' => 'date'),
			'binary' => array('name' => 'binary'),
			'boolean' => array('name' => 'boolean')
		);
	}

}
?>
