<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    protected $videoFile;
    protected $audioFile;
    protected $disk;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post, $videoFile, $audioFile, $disk = 's3')
    {
        $this->post = $post;
        $this->videoFile = $videoFile;
        $this->audioFile = $audioFile;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $outputPath = 'zunger/users/videos/' . uniqid() . '.mp4';

            // Process video with FFMpeg by merging video and audio files
            FFMpeg::fromDisk($this->disk)
                ->open([$this->videoFile, $this->audioFile])
                ->export()
                ->addFormatOutputMapping(new X264, Storage::disk($this->disk)->path($outputPath), ['0:v', '1:a'])
                ->save();

            // Update the post with the new video URL
            $this->post->update([
                'url' => 'https://d1s3gnygbw6wyo.cloudfront.net/' . $outputPath,
            ]);
        } catch (\Exception $e) {
            // Log error or handle failure (could be retried)
            \Log::error("FFMpeg processing failed: " . $e->getMessage());
        }
    }

}
