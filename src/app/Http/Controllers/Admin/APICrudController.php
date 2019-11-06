<?php

namespace gemadigital\framework\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class APICrudController extends Controller
{
    public function ajax()
    {
        $args = func_get_args();
        return call_user_func_array([$this, $args[0] . $args[1]], array_slice($args, 2));
    }

    /*
    |--------------------------------------------------------------------------
    | Default Search
    |--------------------------------------------------------------------------
    */
    public function getSearchParam()
    {
        $request = request();
        return $request ? ($request->has('q') || $request->has('term') ? $request->input('q') . $request->input('term') : false) : false;
    }

    public function entitySearch($entity, $searchFields = null, $where = null, $whereIn = null)
    {
        $search_term = $this->getSearchParam();
        $results = $entity::select();

        if ($search_term && count($searchFields)) {
            $results = $entity::where(function ($query) use ($search_term, $searchFields) {
                $query->where(array_shift($searchFields), 'LIKE', "%$search_term%");

                foreach ($searchFields as $field) {
                    $query->orWhere($field, 'LIKE', "%$search_term%");
                }
            });
        }

        if ($where) {
            foreach ($where as $key => $value) {
                $results = $results->where($key, $value);
            }
        }

        if ($whereIn) {
            foreach ($whereIn as $key => $value) {
                $results = $results->whereIn($key, $value);
            }
        }

        return $results->paginate(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Example
    |--------------------------------------------------------------------------
    */
    public function exampleSearch()
    {
        return $this->entitySearch(Example::class, ['name', 'content']);
    }

    public function exampleFilter()
    {
        return $this->exampleSearch()->pluck('name', 'id');
    }
}
