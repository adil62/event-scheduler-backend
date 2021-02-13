<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\CreateRecurringSchedules;
use \Illuminate\Http\JsonResponse;

/**
 * API's for event creaetion and listing.
 */
class EventController extends Controller
{ 
    /**
     * Create event + 90days schedule.
     */
    public function create(Request $request) : JsonResponse {
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
            CreateRecurringSchedules::dispatch($event , $dayOfWeek);

            return response()->json([
                'message' => 'successfully created event.',
                'event' => $event
            ]);
        } catch(\Throwable $e) {
            report($e);
           
            return response()->json([
                'message' => 'failed creating event.',
            ], 400);
        }
    }

    /**
     * Return all schedules for the current user.
     */
    public function index() : JsonResponse {
        $schedules = Event::leftJoin('schedules', 'schedules.event_id', 'events.id')
        ->where('user_id', auth()->user()->id)
        ->get();

        return response()->json([
            'schedules' => $schedules
        ]);
    }
}