<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


/**
 * Store a single uploaded file in a directory based on the current year.
 *
 * @param Request $request
 * @param string $inputName
 * @param string $directory
 * @return string|null
 */
if (!function_exists('storeSingleFile')) {
    function storeSingleFile(Request $request, $inputName, $directory = 'files/')
        {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $yearDirectory = $directory . "/" . date('Y');
                $filePath = 'storage/' . $file->store($yearDirectory);
                return $filePath;
            }
            
            return null;
        }
    }

/**
 * Update a single file and delete the old one if necessary.
 *
 * @param Request $request
 * @param $attachment
 * @param string $inputName
 * @param string $directory
 * @return string|null
 */

 if (!function_exists('updateSingleFile')) {
     function updateSingleFile(Request $request, $attachment, $inputName, $directory = 'files/'){
        if ($request->hasFile($inputName)) {
           // Path of the file to delete
            $filePathToDelete = $attachment ? str_replace('storage/', '', $attachment->$inputName) : null;

            // Check if file exists and delete it
            if ($filePathToDelete && Storage::exists($filePathToDelete)) {
                Storage::delete($filePathToDelete);
            }

            // Store the new file
            $file = $request->file($inputName);
            $yearDirectory = $directory . "/" . date('Y');
            $filePath = 'storage/' . $file->store($yearDirectory);
            return $filePath;
        }

        return $attachment->$inputName ?? null;
    }
}

 if (!function_exists('unlinkSingleFile')) {
     function unlinkSingleFile($attachment){
        if ($attachment) {
           // Path of the file to delete
            $filePathToDelete = $attachment ? str_replace('storage/', '', $attachment) : null;

            // Check if file exists and delete it
            if ($filePathToDelete && Storage::exists($filePathToDelete)) {
                Storage::delete($filePathToDelete);
            }
        }
        return null;
    }
}

if (!function_exists('nowMinutes')) {
    /**
     * Calculate the difference in minutes between now and a given timestamp.
     *
     * @param  string|\DateTimeInterface  $timestamp
     * @return int
     */
    function nowMinutes($timestamp)
    {
        $result = (int) Carbon::now()->diffInMinutes(Carbon::parse($timestamp));
        return abs($result);
    }
}

