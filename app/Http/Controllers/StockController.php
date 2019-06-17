<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Classes\CrudController;

class StockController extends CrudController
{

    public function fields_for_list() {
    	return ['symbol', 'created_at', 'latest_trading_day', 'open', 'high', 'low', 'price'];
    }

    public function fields_for_show() {
        return ['symbol', 'created_at', 'latest_trading_day', 'open', 'high', 'low', 'price', 'previous_close', 'volumn', 'change', 'change_percentage'];
    }

    public function fields_for_filter() {
    	return ['symbol', 'latest_trading_day'];
    }

    public function fields_for_search_filter() {
        return ['symbol'];
    }

    public function can_add() {
        return 'false';
    }
    public function can_edit() {
        return 'false';
    }

    public function default_filters() {
        $user = $this->user();
        return ["user_id"=>$user->id];
    }

    public function index_action(){
        $controller = $this;
        return view('home', compact("controller"));
    }

    public function quote_action(Request $request) {
        $fields = $request->input("data");

        if (!isset($fields['symbol'])) {
            return response("Unvalid symbol", 500)
            ->header('Content-Type', 'text/plain');
        }

        $object = $this->object();
        $object->user_id = $this->user()->id;
        $object->symbol = $fields['symbol'];
        $object->open = $fields['open'];
        $object->high = $fields['high'];
        $object->low = $fields['low'];
        $object->price = $fields['price'];
        $object->volume = $fields['volume'];
        $object->latest_trading_day = $fields['latest trading day'];
        $object->previous_close = $fields['previous close'];
        $object->save();

        return response('OK',200);
    }
}
