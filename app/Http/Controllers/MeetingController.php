<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Meeting;
use Carbon\Carbon;
use Response;

class MeetingController extends Controller
{

    public function __construct()
    {

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $meetings = Meeting::all();

      foreach ($meetings as $meeting) {
        $meeting->view_meeting = [
            'href'=> 'api/v1/meeting/'.$meeting->id,
            'method'=> 'GET'
        ];
      }
      $response = [
        'msg'=> 'List of all Meetings',
        'meetings'=> $meetings
      ];

      return  response()->json($response, 200);

        return "All Meetings";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $this->validate($request, [
            'title'=> 'required|min:8',
            'description'=> 'required|min:20',
            'user_id'=> 'required|numeric',
            'time'=> 'required|date_format:YmdHie',

        ]);

        $meeting = new Meeting([
          'title'=>$title,
          'description'=>$description,
          'time'=> Carbon::createFromFormat('YmdHie', $time)
        ]);

        if($meeting->save()) {
          $meeting->users()->attach($user_id);
          $meeting->view_meeting = [
              'href' => 'api/v1/meeting/'.$meeting->id,
              'method'=> 'GET'
          ];
          $message = [
              'href' => 'Meeting created',
              'meeting' => $meeting
          ];

          return response()->json($message, 201);
        }

        $response = [
          'msg'=> 'Error during creation' ];

        return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();;
      // $meeting = Meeting::firstOrFail();
      $meeting->view_meeting = [
          'href'=> 'api/v1/meeting/'.$meeting->id,
          'method'=> 'GET'
        ];
        $response = [
            'msg'=> 'Meeting information',
            'meeting'=> $meeting
        ];
      //
      return response()->json($response, 200);
      // return "Single Meeting";
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title'=> 'required',
            'description'=> 'required',
            'time'=> 'required|date_format:YmdHie',
            'user_id' => 'required'
        ]);

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = Meeting::with('users')->findOrFail($id);

        if(!$meeting->users()->where('user_id', $user_id)->first()) {
            return response()->json(['msg'=>'user not registered for meeting, update'], 401);
        }

        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = title;
        $meeting->description = $description;

        if(!$meeting->update()) {
            return response()->json(['msg'=> 'Error during updating'], 404);
        }
        $meeting->view_meeting = [
            'href'=> 'api/v1/meeting/1',
            'method'=> 'GET'
          ];

        $response = [
            'msg'=> 'Meeting updated',
            'mmeting'=> $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $users = $meeting->users;
        $meeting->users()->detach();

        if(!$meeting->delete()) {
            foreach ($users as $user) {
               $meeting->users()->attach($user);
            }
            return response()->json(['msg' => 'deletion failed'], 404);
        }

        $response = [
          'msg'=> 'Meeting deleted',
          'create'=> [
              'href'=> 'api/v1/meeting',
              'method'=> 'POST',
              'params'=> 'title, description, time'
          ]
        ];

        return response()->json($response, 200);
    }
}
