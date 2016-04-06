<?php

namespace App\Http\Controllers;

use App\Experiment;
use App\Http\Requests;
use Illuminate\Http\Request;

class CrudExperimentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $experiments = Experiment::paginate(15);

        return view('experiment.index', compact('experiments'));
    }
}
