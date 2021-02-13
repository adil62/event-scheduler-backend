<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schedule;

class CreateRecurringSchedules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventId;
    protected $dayOfWeek;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventId, $dayOfWeek)
    {
        $this->eventId = $eventId;
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventStartDate =  date('Y-m-d', strtotime("next {$this->dayOfWeek}"));
        $eventEndDate = date('Y-m-d', strtotime('today + 90 days'));

        $eventEndDateInTimestamp = strtotime($eventEndDate); // 90days from now
        $calculatedScheduleDatesForNext90Days[] = $eventStartDate; // stores all the recurring dates upto 90days from now.

        // eg.if weekday=friday then calculate next fridays date then the next.. 
        $eventNextScheduleTimestamp = strtotime($eventStartDate); // stores the week date computed on the last iteration.
        while ($eventNextScheduleTimestamp < $eventEndDateInTimestamp) {
            $eventNextScheduleTimestamp = strtotime(date("Y-m-d", $eventNextScheduleTimestamp) . "+1 week");
            if ($eventNextScheduleTimestamp < $eventEndDateInTimestamp) {
                $calculatedScheduleDatesForNext90Days[] = date("Y-m-d" , $eventNextScheduleTimestamp);
            }
        }
        
        // save each recurring event into db.
        foreach($calculatedScheduleDatesForNext90Days as $scheduleDate) {
            $schedule = new Schedule;
            $schedule->event_id = $this->eventId ;
            $schedule->schedule_date = $scheduleDate;
            $schedule->save();
        }
    }
}
