<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;

class FileUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $photoPath;

    public function __construct($photoPath)
    {
        $this->photoPath = $photoPath;
    }

    public function handle()
    {
        $path = 'Magicline Database/images/product/' . basename($this->photoPath);

        Storage::disk('dropbox')->put($path, file_get_contents($this->photoPath));

        // You can perform any additional processing or record-keeping here
    }

    // public function handle()
    // {
    //     $photo = $this->photo;

    //     $photoName = time() . '_' . $photo->getClientOriginalName();
    //     $path = 'Magicline Database/images/product/' . $photoName; // Relative path within Dropbox

    //     Storage::disk('dropbox')->put($path, file_get_contents($photo));
    // }

    public function onQueue()
    {
        return 'file_upload_queue'; // Specify the desired queue name
    }
}
