<?php

namespace App\Mail;

use App\Enums\ResultDisplayMode;
use App\Models\AssessmentAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssessmentPublished extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public AssessmentAttempt $attempt;

    public function __construct($attempt)
    {
        $this->attempt = $attempt;
    }

    public function build(): AssessmentPublished
    {
        $showDetailsButton = false;
        $url = config('app.url').'/tests';
        if ($this->attempt->assessment->result_display_mode === ResultDisplayMode::DisplayMarkAndAnswers) {
            $url .= '/'.$this->attempt->assessment_id.'/results'.'/'.$this->attempt->id;
            $showDetailsButton = true;
        }

        return $this->subject('Your Assessment Results are Ready')
            ->view('emails.assessmentPublished')
            ->with([
                'url' => $url,
                'attempt' => $this->attempt,
                'showDetailsButton' => $showDetailsButton,
            ]);
    }
}
