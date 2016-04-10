<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Classes\Traits\ApiRespondable;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;



class ApiController extends Controller
{
	use ApiRespondable;

    protected $fractal;

	public function __construct(Manager $fractal)
	{
		$this->fractal = $fractal;

        $include = Input::get('include');
        if(isset($include)) {
            $this->fractal->parseIncludes($include);
        }
	}

	protected function respondWithItem($item, $callback,$resourceKey = null)
    {
        $resource = new Item($item, $callback,$resourceKey);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }
    protected function respondWithCollection($collection, $callback, $resourceKey = null)
    {
        $resource = new Collection($collection, $callback,$resourceKey);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }
}
