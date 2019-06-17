<?php

namespace App\Http\Classes;

use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelObject extends Model	{

	use EloquentJoin;

	protected $useTableAlias = false;
	protected $appendRelationsCount = false;
	protected $leftJoin = false;
	protected $aggregatedMethod = 'MAX';

	public static function get_table_name() {
		$class_name = get_called_class();
    	$object = new $class_name();

    	return $object->getTable();
	}

	public static function true_field_name($name) {
		$table = self::get_table_name();
		return $table.".".$name;
	}

  public static function columns() {
			$class_name = get_called_class();
    	$object = new $class_name();

    	return Schema::getColumnListing($object->getTable());
	}

	public static function model_name() {
        $name = get_called_class();
        $name = explode("\\", $name);

        return strtolower(array_last($name));
	}
}
