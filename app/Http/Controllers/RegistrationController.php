<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class RegistrationController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
          'meeting_id'=> 'required',
          'user_id' =>'requerid'  
      ]);

      $meeting_id = $request->input('meeting_id');
      $user_id = $request->input('user_id');

      $meeting = [
        'title'=>$title,
        'description'=>$description,
        'time'=>$time,
        'view_meeting'=> [
          'href'=> 'api/v1/meeting/1',
          'method'=> 'GET'
        ]
      ];

      $user = [
        'name'=> 'Name'
      ];

      $response = [
        'msg'=> 'User resgitering for meeting',
        'meeting'=> $meeting,
        'user' => $user,
        'unregister' => [
            'href'=> 'api/v1/meeting/resgistration/1',
            'method'=> 'DELETE'
        ]
      ];

      return  response()->json($response, 201) ;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return  "Registration show works";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return  "Registration edit: ".$id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $meeting = [
        'title'=>'Title',
        'description'=>'Description',
        'time'=>'Time',
        'view_meeting'=> [
          'href'=> 'api/v1/meeting/1',
          'method'=> 'GET'
        ]
      ];

      $user = [
        'name'=> 'Name'
      ];

      $response = [
        'msg'=> 'User unresgitering for meeting',
        'meeting'=> $meeting,
        'user' => $user,
        'unregister' => [
            'href'=> 'api/v1/meeting/resgistration/1',
            'method'=> 'POST',
            'params'=> 'user_id, meeting_id'
        ]
      ];

      return response()->json($response, 200);

    }
}
