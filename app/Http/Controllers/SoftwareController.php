<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Software;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;

class SoftwareController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $software = Software::paginate(15);

        return view('software.index', compact('software'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('software.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        
        Software::create($request->all());

        Session::flash('flash_message', 'Software added!');

        return redirect('software');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function show($id)
    {
        $software = Software::findOrFail($id);

        return view('software.show', compact('software'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $software = Software::findOrFail($id);

        return view('software.edit', compact('software'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        
        $software = Software::findOrFail($id);
        $software->update($request->all());

        Session::flash('flash_message', 'Software updated!');

        return redirect('software');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        Software::destroy($id);

        Session::flash('flash_message', 'Software deleted!');

        return redirect('software');
    }

}
