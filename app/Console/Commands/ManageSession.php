<?php

namespace App\Console\Commands;

use App\Mail\LecturerSessionEnded;
use App\Mail\LecturerSessionStarted;
use App\Mail\StudentSessionEnded;
use App\Mail\StudentSessionStarted;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ManageSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manage:session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to manage session statuses and send notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scheduledSessions = Session::where('starts_at', '<=', Carbon::now())
            ->where('status', 'Scheduled')
            ->get();

        foreach ($scheduledSessions as $scheduledSession) {
            $scheduledSession->update(['status' => 'Running']);
            $this->sendStartedSessionMail($scheduledSession);
        }

        $endedSessions = Session::where('ends_at', '<=', Carbon::now())
            ->where('status', 'Running')
            ->get();

        foreach ($endedSessions as $endedSession) {
            $endedSession->update(['status' => 'Ended']);
            $this->sendEndedSessionMail($endedSession);
        }

        return 0;
    }

    public function sendStartedSessionMail($data)
    {
        // Send mail notification to lecturer
        Mail::to($data->lecturer->email)->queue(new LecturerSessionStarted($data));

        // Send mail notification to attendees
        $classe = $data->classe;

        $classe->student()->chunk(10, function ($students) use ($data) {
            foreach ($students as $student) {
                // Send email to each student in the chunk
                Mail::to($student->email)->queue(new StudentSessionStarted($data));
            }
        });
    }

    public function sendEndedSessionMail($data)
    {
        // Send mail notification to lecturer
        Mail::to($data->lecturer->email)->queue(new LecturerSessionEnded($data));

        // Send mail notification to attendees
        $classe = $data->classe;

        $classe->student()->chunk(10, function ($students) use ($data) {
            foreach ($students as $student) {
                // Send email to each student in the chunk
                Mail::to($student->email)->queue(new StudentSessionEnded($data));
            }
        });
    }
}
