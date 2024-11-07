<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
class ProcessProfileVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoPath;
    protected $outputPath;
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($videoPath, $outputPath, $user)
    {
        $this->videoPath = $videoPath;
        $this->outputPath = $outputPath;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Video to GIF conversion using FFMpeg
        FFMpeg::fromDisk('local')
            ->open($this->videoPath)
            ->addFilterAsComplexFilter(
                ['-ss 0', '-t 3'],
                [
                    '-vf "fps=10,scale=360:-1:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse"',
                    '-loop 0',
                ]
            )
            ->export()
            ->toDisk('s3')
            ->save(
                $this->outputPath
            );
        // Update the user's profile image to the GIF URL
        $this->user->update([
            'profile_image' => 'https://d1s3gnygbw6wyo.cloudfront.net' . $this->outputPath,
        ]);
    }
}
