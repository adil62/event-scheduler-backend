<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Schedule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_creates_and_displays_events_and_recurring_schedules()
    { 
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/event', [
            "dayOfWeek" => "monday" ,
            "name" => "test-name",
            "desc" => "test-desc",
            "startTime" => "08:30",
            "endTime" => "09:00"
        ]);
 
        // event: db should have a event record
        $this->assertDatabaseHas('events', [
            "day_of_the_week" => "monday" ,
            "name" => "test-name",
            "desc" => "test-desc",
            "user_id" =>  $user->id,
            "start_time" => "08:30",
            "end_time" => "09:00"
        ]);
        $dayOfWeek = 'monday';
         
        // schedules: db should have n schedules created with correct date.
        $eventStartDate =  date('Y-m-d', strtotime("next {$dayOfWeek}"));
        $eventEndDate = date('Y-m-d', strtotime('today + 90 days'));
        $schedules = $this->getScheduleDates($eventStartDate, $eventEndDate);

        foreach ($schedules as $scheduleDate) {
            $this->assertDatabaseHas('schedules', [
                'event_id' => 1,
                'schedule_date' => $scheduleDate
            ]);    
        }
        $response->assertStatus(200);

        // asserting if it successfully displays the created schedules.
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/event');

        $response->assertJson([
            'schedules' => Schedule::all()->toArray()
        ]);
    }
    
    // helpers :
    private function getScheduleDates($eventStartDate, $eventEndDate) {
        $eventEndDateInTimestamp = strtotime($eventEndDate);
        $calculatedScheduleDatesForNext90Days[] = $eventStartDate;

        // eg.if weekday=friday then calculate next fridays date then the next.. 
        $eventNextScheduleTimestamp = strtotime($eventStartDate);
        while ($eventNextScheduleTimestamp < $eventEndDateInTimestamp) {
            $eventNextScheduleTimestamp = strtotime(date("Y-m-d", $eventNextScheduleTimestamp) . "+1 week");
            if ($eventNextScheduleTimestamp < $eventEndDateInTimestamp) {
                $calculatedScheduleDatesForNext90Days[] = date("Y-m-d" , $eventNextScheduleTimestamp);
            }
        }
        
        return $calculatedScheduleDatesForNext90Days;
    }
}
