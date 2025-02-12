<?php

namespace App\Jobs;

use App\Models\Crime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use LakM\Comments\Models\Comment;

class CommentSentiment implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Comment $comment,
        public Crime $crime,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::post('https://crime-image-caption-generator-api.onrender.com/analyze_comment_relevance', [
            'comment_text' => $this->comment->text,
            'crime_post_text' => $this->crime->description,
        ]);

        if ($response->json('relevance', 'Positive') == 'Positive') {
            $this->comment->relevance = 1;
        } else {
            $this->comment->relevance = -1;
        }

        $this->comment->save();
    }
}
