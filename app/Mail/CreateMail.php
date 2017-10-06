<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;


class CreateMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $param;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email =  $this->view($this->param['Views'])
            ->subject($this->param['Subject'])
            ->with([
            'Vars' => $this->param,
        ]);

        if($this->param['attachment']){
            foreach($this->param['attachment'] as $filePath){
                    $email->attach($filePath);
                }
        }

        return $email;
    }
}
