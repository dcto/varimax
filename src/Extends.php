<?php
use \Illuminate\Support\Arr;
use \Illuminate\Support\Str;
use Illuminate\Support\Collection;
/**
 * check the array keys exist
 * @param $array
 * @param $key
 * @return bool
 */
Arr::macro('have', function($array, $key){
    if(is_array($key)){
        foreach ($key as $k) {
            if(!isset($array[$k])) return false;
        }
        return true;
    }
    return isset($array[$key]);
});


/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param  array   $array
 * @param  string  $prepend
 * @return array
 */
Arr::macro('dot', function($array, $prepend = '', $trim = null)
{
    $arr = [];
    foreach ($array as $key => $value) {
        if (is_array($value) && ! empty($value)) {
            $arr = array_merge($arr, static::dot($value, $prepend. ($trim ? trim($key, $trim) : $key).'.'));
        } else {
            $arr[$prepend.$key] = $value;
        }
    }
    return $arr;
});


/**
 * array Undot
 * @param $dotNotationArray
 * @return array
 */
Arr::macro('undot', function($dotNotationArray){
    $array = [];
    foreach ($dotNotationArray as $key => $value) {
        static::set($array, $key, $value);
    }
    return $array;
});


Collection::macro('tree', function($name = 'sub', $col = 'pid'){
    $trees = [];
    $items = $this->keyBy('id')->toArray();
    foreach($items as $k => $item){
        if(isset($items[$item[$col]])){
            $items[$item[$col]][$name][] = &$items[$k];
        }else{
            $trees[] = &$items[$k];
        }
    }
    return $trees;
 });

//Get parent with $id
Collection::macro('top', function($id, $col = 'pid'){
   return $this->where('id', $id)->first();
});


//Get all parents with $id
Collection::macro('tops', function($id, $col = 'pid'){
    $parents = collect([]);
    $parent = $this->where('id', $id)->first();
    while(!is_null($parent)) {
        $parents->push($parent);
        $parent = $this->where('id', $parent[$col])->first();
    }
    return $parents;
});

//Get child with $id
Collection::macro('sub', function($id = 0, $col = 'pid'){
    return $this->where($col, $id);
});

//Get all childs with $id
Collection::macro('subs', function($id = 0, $col = 'pid'){
    $childs = collect([]);
    $child = $this->where($col, $id);
    while($child->count()){
        $childs->push(...$child);
        $child = $this->whereIn($col, $child->pluck('id'));
    }
    return $childs;
});