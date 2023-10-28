<?php

namespace VM\Services;

use Illuminate\Support\Collection;

/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: Collection Macros
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2023-03-20 21:49
 * SITE: https://www.varimax.cn/
 */

class MacroableServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerNestCollect();
    }

    /**
     * 数组扩展方法
     * collection($array)->top($id=1);
     * collection($array)->tops($id=1);
     * collection($array)->sub($pid=1);
     * collection($array)->subs($pid=1);
     * collection($array)->get(['id', 'pid', 'name'])->subs($pid=1)->tree('children');
     * 
     * @author dc.
     * @return array
     * @version 20230320
     */
    protected function registerNestCollect(){
        Collection::macro('top', function($id, $col = 'id'){return $this->where($col, $id);});
        Collection::macro('tops', function($id, $col = 'pid'){
            $parents = collect([]);
            $parent = $this->where('id', $id)->first();
            while(!is_null($parent)) {
                $parents->push($parent);
                $parent = $this->where('id', $parent[$col])->first();
            }
            return $parents;
        });
        Collection::macro('sub', function($id, $col = 'pid'){return $this->where($col, $id);});
        Collection::macro('subs', function($id = 0, $col = 'pid'){
            $childs = collect([]);
            $child = $this->where($col, $id);
            while($child->count()){
                $childs->push(...$child);
                $child = $this->whereIn($col, $child->pluck('id'));
            }
            return $childs;
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
    }
}