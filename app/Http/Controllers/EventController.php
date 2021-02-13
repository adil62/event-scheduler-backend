<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\CreateRecurringSchedules;

class EventController extends Controller
{ 
    public function create(Request $request) {
        try {
            $dayOfWeek = STR::lower($request->dayOfWeek);

            // create event
            $event = new Event;
            $event->name = $request->name;
            $event->user_id = auth()->user()->id;
            $event->desc = $request->desc;
            $event->start_time = $request->startTime;
            $event->end_time = $request->endTime;
            $event->day_of_the_week = $dayOfWeek;
            $event->save();
            
            // generate next 90days schedule in the background.
            CreateRecurringSchedules::dispatch($event->id, $dayOfWeek);

            return response()->json([
                'message' => 'successfully created event.',
                'event' => $event
            ]);
        } catch(\Throwable $e) {
            report($e);
            throw $e;
            return response()->json([
                'message' => 'failed creating event.',
            ], 400);
        }
    }
}
