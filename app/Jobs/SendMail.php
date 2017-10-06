<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Mail\CreateMail;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $param;

/*
 * example param = array(
 *          'Subject'                               => "Your Subject",
 *          'Views'                                 => "your resource file",
 *          'User'                                  => $User,
 *          'To'                                    => $User->email
 * );
 */

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new CreateMail($this->param);
//        dd($email);
        Mail::to($this->param['To'])
            ->send($email);
    }
}

