<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\DeviceType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;

class DeviceTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $devicetype = DeviceType::paginate(15);

        return view('devicetype.index', compact('devicetype'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('devicetype.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        
        DeviceType::create($request->all());

        Session::flash('flash_message', 'Device Type added!');

        return redirect('devicetype');
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
        $devicetype = DeviceType::findOrFail($id);

        return view('devicetype.show', compact('devicetype'));
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
        $devicetype = DeviceType::findOrFail($id);

        return view('devicetype.edit', compact('devicetype'));
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
        
        $devicetype = DeviceType::findOrFail($id);
        $devicetype->update($request->all());

        Session::flash('flash_message', 'Device Type updated!');

        return redirect('devicetype');
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
        DeviceType::destroy($id);

        Session::flash('flash_message', 'Device Type deleted!');

        return redirect('devicetype');
    }

}
