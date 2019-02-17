<?php

namespace ActiveRecord;

require_once __DIR__ . "/PgsqlAdapter.php";

/**
 * Adaptador para quando o protocolo informado é prostgres
 * PDO usa o protocolo pgsql ao inves do postgres informado
 */
class PostgresAdapter extends PgsqlAdapter { 
	
	public function __construct($info)
	{
		$info->protocol = 'pgsql';
		
		parent::__construct($info);
	}
}