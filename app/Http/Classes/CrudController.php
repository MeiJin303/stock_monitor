<?php

namespace App\Http\Classes;

use Illuminate\Http\Request;
use App\Exceptions\Handler;
use App\Http\Controllers\Controller;

class CrudController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

	private function __controller_full_name() {
		return get_called_class();
	}
	private function __controller_name() {
		return controller_class_for_name($this->model_name());
	}

    public static function controller_name($model_name) {
        return controller_class_for_name($model_name);
    }

	public function action($action = null, $parameters = array()) {
        array_unshift($parameters, $this->model_name(), $action);
        return route('crud', $parameters);
	}

    /**
     * Callback Functions
     * Call functions base on prefix and pass the suffix as the argument
     */
	public function __call($name, $arguments) {
        if (strpos($name, 'fields_for_') === 0)
                return call_user_func(array($this, 'fields'), str_replace('fields_for_', '', $name));
        if (strpos($name, 'headings_for_') === 0)
                return call_user_func(array($this, 'headings'), str_replace('headings_for_', '', $name));
        if (strpos($name, 'can_') === 0)
                return call_user_func(array($this, 'can'), str_replace('can_', '', $name));

        return null;
    }

    /**
     * Headings callback function
     */
    protected function headings($action) {
        if (method_exists($this, "headings_for_$action"))
                return call_user_func(array($this, "headings_for_$action"));

        if ($action == 'edit')  return $this->headings_for_add();

        $fields = call_user_func(array($this, "fields_for_$action"));
        foreach ($fields as &$key) {
            $parts = explode('.', $key);
            $key = $parts[0];
            $key = str_proper($key);
        }
        return $fields;
    }

    /**
     * Fields callback function
     */
    protected function fields($action) {
        if (method_exists($this, "fields_for_$action"))
                return call_user_func(array($this, "fields_for_$action"));

        if ($action == 'export') return $this->fields_for_list();
        if ($action == 'search') return $this->fields_for_list();
        if ($action == 'sort') return $this->fields_for_list();
        if ($action == 'filter') return array();
        if ($action == 'edit')  return $this->fields_for_add();

        $class_name = $this->model_class();
        $fields = $class_name::columns();

        return $fields;
    }

    /**
     * Can callback function
     */
	protected function can($action) {
        if ($action == 'sort') return !$this->sort_order();
        if ($action == 'filter') {
            if (count($this->fields_for_filter())>0)
                return 'true';
            else
                return 'false';
        }
        return 'true';
    }

    public function can_copy() {
        return $this->can_add();
    }

    public function fields_for_search_filter() {
        return [];
    }

    public function value_list($key, $object = null) {
        if (in_array($key, $this->fields_for_search_filter()))  return null;

        $data = $this->model_db();

        $match = array();
        preg_match('/(.*)\.(.*)/', $key, $match);
        if (count($match)>0) {
            $field = $this->db_field_name($key, null, $data);
            $data->select("{$field}")->distinct();
        }

        if ($object){
            if (count($match)>0) {
                $data->first();
            }
            return $object->$key;
        }

        return $data->pluck($key)->unique();
    }

    /**
     * Set an array of key-value pairs which save field is different from the fields for action.
     * Array key is the defined field in fields for action.
     * Array value is an array which contians actual field name(change_col), change_object, value column and value object.
     * e.g.
     * {
     *      change_col: "category_id",
     *      change_object: "product",
     *      value_col: "id"
     * }
     * $product->category_id = $category->id;
     *
     */
    public function value_key_list() {
        return [];
    }

    /**
     * Return the actual saved field name base on value_key_list()
     * @$key the defined field name from the fields for action methods.
     */
    private function value_key($key) {
        $value_key_list = $this->value_key_list();

        if (isset($value_key_list[$key]))
            return $value_key_list[$key];

        return null;
    }

    public function identifier() {
        return "id";
    }

    public function edit_field_config($key) {
        return null;
    }

    public function per_page() {
        return 10;
    }

    private $model_class = false;
    private function model_class() {
        if ($this->model_class)    return $this->model_class;

        $name = $this->model_name();
		$name = "App\\HTTP\\Models\\$name";
        if (!class_exists($name))
                die("Model class '$name' was not found. Overried " . __METHOD__ . " in " . get_class($this) . " to return the correct model class name");
        $this->model_class = $name;
        return $name;
    }

    public function buttons_for_show($object = null) {
        return array();
    }

    public function buttons_for_list() {
        return array();
    }

    /**
     *  Configure customized links for rows in list view
     */
    public function links_for_list_row($object = null) {
        return array();
    }

    private $model_name = false;
    public function model_name() {
        if ($this->model_name)  return $this->model_name;

        $name = get_class($this);
        $name = explode("\\", $name);
        $name = str_replace(CONTROLLER_SUFFIX, "", end($name));

        $this->model_name = $name;
        return $name;
    }

    public function model_db() {
        $class = $this->model_class();
        return $class::query();
    }

    public function object($id=null) {
        $class = $this->model_class();
        return $id ? $class::find($id) : new $class();
    }

    public function field_configs($action = null) {
        $configs = array();
        $actions = $action ? [$action] : ['list', 'show'];
        if (!$action && $this->can_filter())  $actions[] = "filter";
        if (!$action && $this->can_add())  $actions[] = "add";
        if (!$action && $this->can_edit())  $actions[] = "edit";

        foreach ($actions as $a) {
            $fields = call_user_func(array($this, 'fields'), str_replace('fields_for_', '', $a));
            $headings = call_user_func(array($this, 'headings'), str_replace('headings_for_', '', $a));
            $c = [];
            foreach($fields as $key => $name) {
                if (!$name) continue;

                $title = null;
                if (isset($headings[$key]) && $headings[$key])  $title = $headings[$key];

                switch($a) {
                    case 'list':
                        $sortField = false;

                        $tmp = $name;
                        if (str_contains($name, "."))   $name = strtolower($title);

                        if (in_array($tmp, $this->fields_for_sort())) $sortField = $name;
                        $c[$name] = compact("name", "title", "sortField");
                        break;
                    case 'filter':
                        $values = $this->value_list($name);
                        $c[$name] = compact("name", "title", "values");
                        break;
                    case 'add':
                    case 'edit':
                        $fc = $this->edit_field_config($name);
                        $c[$name] = compact("name", "title");
                        if ($fc) {
                            $c[$name] = array_merge($c[$name], $fc);
                        }
                        break;
                    default:
                        $c[$name] = compact("name", "title");
                        break;
                }
            }

            if ($a =="list" && !in_array("id", $fields))
                $c["id"] = array("name"=>"id", "visible"=>false);

            if ($a == "add" || $a == "edit") {
                $auto_fields = ['id', 'created_at', 'updated_at'];
                foreach($auto_fields as $n) {
                    if(in_array($n, $fields)) {
                        unset($c[$n]);
                    }
                }
            }

            $configs[$a] = $c;
        }

        return $configs;
    }

    private function db_field_name($field, $heading=null, &$data = null) {
        $match = array();
        preg_match('/(.*)\.(.*)/', $field, $match);

        if (count($match)>0) {
            if ($data) {
                $data = $data->joinRelations($match[1]);
            }

            $model_class = "App\\Http\\Models\\".ucwords(str_singular_alg(strtolower(array_last(explode(".", $match[1])))));
            $field_name = $model_class::true_field_name($match[2]);

            if ($heading) {
                $field = "{$field_name} as {$heading}";
            } else {
                $field = $field_name;
            }
        } else {
            $field = $this->model_class()::true_field_name($field);
        }

        return $field;
    }

    public function default_filters() {
        return [];
    }

    private function apply_default_filters($data) {
        foreach($this->default_filters() as $field => $value) {
            $field = $this->db_field_name($field);
            $data = $data->where($field, "=", $value);
        }
        return $data;
    }

    public function fetch_objects_action($json = false, Request $request) {
    	$class = $this->model_class();
    	if (!$json)	return $class::all();

        $sort = $request->input('sort');
        $filter = $request->input('filter');
        $page = $request->input('page');
        $page = $page ? $page : 1;
    	$per_page = $request->input('per_page');
        $per_page = $per_page == -1 || $per_page > 0 ? $per_page : $this->per_page();
    	$search = trim($request->input('search'));

        $table = $class::get_table_name();
        $data = $this->model_db();
        $select = array();

        foreach((array) $this->fields_for_list() as $key => $field) {
            if (!$field)	continue;

            $headings = $this->headings_for_list();
            $heading = $headings && is_array($headings) ? strtolower($headings[$key]) : $field;

            $select[] = $this->db_field_name($field, $heading, $data);
        }

        if (!in_array("{$table}.id", $select))
            array_unshift($select, "{$table}.id");

        if (count($select))
            $data = $data->select($select);

        if ($search) {
        	foreach($this->fields_for_search() as $field) {
                if (!$field)	continue;
                $field = $this->db_field_name($field);
        		$data = $data->orWhere($this->db_field_name($field), 'like', "%$search%");
        	}
        }


        if ($filter) {
            $filters = (array) explode(",", $filter);
            foreach($filters as $f) {
                if (!$f)    continue;
                $f = explode("|", $f);
                if (!isset($f[1]) || !$f[1]) continue;

                $data = $data->where($this->db_field_name($f[0]), "like", "%{$f[1]}%");
            }
        }

        $data = $this->apply_default_filters($data);

        if ($sort) {
            $sort = (array) explode(",", $sort);
            foreach($sort as $s) {
                if (!$s)    continue;
                $s = explode("|", $s);
                $field  = $s[0];

                $order = isset($s[1]) ? $s[1] : "desc";
                $data = $data->orderBy($field, $order);
            }
        }

        $data->orderBy("{$table}.id", "desc");

        if ($per_page == -1)
            $per_page = $data->count();

        return $data->paginate($per_page)->toJson();
    }

    public function index_action() {
		return __CLASS__;
    }

    public function list_action() {
        $controller = $this;
    	return view("crud.list", compact("controller"));
    }

    public function show_action($id) {
        $class = $this->model_class();
        $object = $class::find($id);

        foreach((array) $this->fields_for_show() as $field) {
            $match = array();
            preg_match('/(.*)\.(.*)/', $field, $match);
            if (count($match)>0) {
                $join_model = strtolower(array_last(explode(".", $match[1])));
                $object->load($join_model);
            }
        }

    	return $object->toJson();
    }

    private function edit_object($object, $fields, $request, $relations = null) {

        $inputs = $request->all();
        $relations = (array) $relations;

        foreach($fields as $name) {
            if (in_array($name, ["id", "created_at", "updated_at"]))
                continue;

            if (isset($relations[$name]) && $relations[$name]) {
                $rel = explode(".", $name);
                $rel_field = array_pop($rel);
                $rel_model = end($rel);

                $value_key = $this->value_key($name);
                if (!$value_key) {
                    $obj = $object;
                    foreach((array) $rel as $m) {
                        $obj = $obj->m;
                    }
                    try {
                        $obj->$rel_field = $request->input($name);
                        $obj->save();
                    } catch(\Exception $e) {
                        return response((String) $e->getMessage(), 500)
                              ->header('Content-Type', 'text/plain');
                    }
                    continue;
                }

                /**
                 * value_key structure. e.g.
                 * {
                 *      change_col: "category_id",
                 *      change_object: "product",
                 *      value_col: "id"
                 * }
                 * $product->category_id = $category->id;
                 */

                try {
                    $val_class = "App\Models\\".ucwords(strtolower($rel_model));
                    $val = $val_class::where($rel_field, $inputs[$name])->first();
                } catch(\Exception $e) {
                    return response((String) $e->getMessage(), 500)
                          ->header('Content-Type', 'text/plain');
                }

                if (!$val) {
                    return response("Can't find matched object '$rel_model' which '$rel_field' = '".$inputs[$name]."'", 500)
                    ->header('Content-Type', 'text/plain');
                }

                $vk = $value_key["value_col"];
                $val = $val->$vk;

                if (strtolower(get_class($object)::model_name()) == strtolower($value_key["change_object"])) {
                    $vk = $value_key["change_col"];
                    $object->$vk = $val;
                } else {
                    $obj = $object;
                    foreach((array) $rel as $m) {
                        $obj = $obj->m;
                        if (strtolower(get_class($obj)::model_name()) == strtolower($value_key["change_object"])) {
                            $vk = $value_key["change_col"];
                            $obj->$vk = $val;
                            try {
                                $obj->save();
                            } catch(\Exception $e) {
                                return response((String) $e->getMessage(), 500)
                                      ->header('Content-Type', 'text/plain');
                            }
                            break;
                        }
                    }
                }

            } else {
                $object->$name = $inputs[$name];
            }
        }

        try {
            $object->save();
        } catch(\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        return true;
    }

    public function add_action(Request $request) {
    	if ($request->method() != 'POST')
            return response('Invalid Request. Please try again.', 500)
                  ->header('Content-Type', 'text/plain');

        $class = $this->model_class();
        $object = new $class();

        $relations = array();
        $fields = $this->fields_for_add();
        foreach($fields as $name) {
            if (in_array($name, ['id', 'created_at', 'updated_at']))    continue;

            $match = array();
            preg_match('/(.*)\.(.*)/', $name, $match);
            if (count($match) > 0) {
                $relations[$name] = $match[1];
            }
        }

        if (!$this->edit_object($object, $fields, $request, $relations))
            return response("Fail to create a new '".$this->model_name()."'", 500)
            ->header('Content-Type', 'text/plain');

        return response("Add New '".$this->model_name()."' with #".$object->{$this->identifier()}, 200)
                ->header('Content-Type', 'text/plain');
    }

    public function edit_action($id, Request $request) {

        $class = $this->model_class();

        $relations = array();
        $fields = $this->fields_for_edit();
        foreach($fields as $name) {
            if (in_array($name, ['id', 'created_at', 'updated_at']))    continue;

            $match = array();
            preg_match('/(.*)\.(.*)/', $name, $match);
            if (count($match) > 0) {
                $relations[$name] = $match[1];
            }
        }

        try {
            $object = count($relations) ? $class::with(array_values($relations))->find($id) : $class::find($id);
        } catch(\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        if ($request->method() != 'POST') {
            return $object;
        }

        if (!$this->edit_object($object, $fields, $request, $relations))
            return response("Fail to save changes to '".$this->model_name()." #'".$object->{$this->identifier()}, 500)
            ->header('Content-Type', 'text/plain');

        return response("Saved changes to '".$this->model_name()." #'".$object->{$this->identifier()}." successfully.", 200)
                ->header('Content-Type', 'text/plain');
    }

    public function copy_action($id, Request $request) {
        $class = $this->model_class();
        try {
            $object = $class::find($id);
        } catch(\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        if (!$object) {
            return response("Can not find $class with ID #$id!", 500)
                  ->header('Content-Type', 'text/plain');
        }

        $new = new $class();
        foreach($this->fields_for_copy() as $field) {
            if (in_array($field, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by']))
                continue;
            $new->$field = $object->$field;
        }

        try {
            $new->save();
        } catch(\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        return $new->id;
    }

    public function remove_action($id, Request $request) {
        $class = $this->model_class();

        try {
            $object = $class::find($id);
        } catch(\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        try {
            $object->delete();
        } catch (\Exception $e) {
            return response((String) $e->getMessage(), 500)
                  ->header('Content-Type', 'text/plain');
        }

        return response("Removed '".$this->model_name()."' with ID $id.", 200)
                ->header('Content-Type', 'text/plain');
    }

    public function user() {
        return auth()->user();
    }
}
