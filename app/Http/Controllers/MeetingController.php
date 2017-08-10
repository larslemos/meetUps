<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Meeting;
use Carbon\Carbon;
use Response;
use JWTAuth;


class MeetingController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
          'update', 'store', 'destroy'
        ]]);
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
        $this->validate($request, [
            'title'=> 'required|min:8',
            'description'=> 'required|min:20',
            'time'=> 'required|date_format:YmdHie',
        ]);

        if(!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

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
            'description'=> 'sometimes|required',
            'time'=> 'sometimes|required|date_format:YmdHie'
        ]);

        if(!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 400);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::with('users')->findOrFail($id);

        if(!$meeting->users()->where('user_id', $user_id)->first()) {
            return response()->json(['msg'=>'user not registered for meeting, update not successful'], 401);
        }

        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
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

        if(!$user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['msg' => 'User not found'], 404);
        }
        if(!$meeting->user()->where('users.id', $user->id)->first()) {
          return response()->json(['msg' => 'user not registered for meeting, update not succesful'], 401);
        }

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
