<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;
require_once __DIR__ . "/PgsqlAdapter.php";

/**
 * Alias to Pgsql Adapter
 * Some database applications use postgres protocol
 *
 * @package ActiveRecord
 */
class PostgresAdapter extends PgsqlAdapter {

    public function __construct($info)
    {
        $info->protocol = 'pgsql';

        parent::__construct($info);
    }
}