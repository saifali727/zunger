<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;
class SongsController extends Controller
{
    public function upload_songs(Request $request){
        $audio_url= "";
        $song_image="";
        if ($request->hasFile('song')) {
            $tempAudioFile = 'temp_' . uniqid() . '.mp3';
            $path = "uploads/audios";

            // Assuming $request->file('song') returns the uploaded file
            $uploadedFile = $request->file('song');

            // Store the uploaded file using S3 disk
            $storedFilePath = Storage::disk('s3')->putFileAs($path, $uploadedFile, $tempAudioFile);

            if ($storedFilePath) {
                // Construct the S3 URL
                $audio_url = Storage::disk('s3')->url($storedFilePath);

                // Now you can save $audio_url to your database or use it as needed
            }
        }
        if($request->hasFile('image')){
            $image = $request->file('image');
            $file_path =  Storage::disk('s3')->put('public/songs_images', $image);
            $file_path = Storage::disk('s3')->url($file_path);
            $song_image = $file_path;
        }

            $song =  Song::create([
                'title'=>$request->title,
                'url'=>$audio_url,
                'image_url'=>$song_image,
                'duration'=>$request->duration,
            ]);
        return response()->json([
            'status'=>200,
            'music'=>$song,
        ]);

    }

    public function get_songs(Request $request){
        $songs = Song::all();
        return response()->json([
            'status'=>200,
            'songs'=>$songs,
        ]);
    }

}
