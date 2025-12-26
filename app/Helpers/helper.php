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
    function storeSingleFile(Request $request, string $inputName, string $directory = 'files')
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }

        $yearDirectory = $directory . '/' . date('Y');

        // store in storage/app/public/...
        $path = $request->file($inputName)->store($yearDirectory, 'public');

        // return path for DB (without "storage/")
        return $path;
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
    function updateSingleFile(
        Request $request,
        string $inputName,
        string $directory = 'files',
        ?string $oldFile = null
    ) {
        // নতুন file না থাকলে আগেরটাই রাখো
        if (!$request->hasFile($inputName)) {
            return $oldFile;
        }

        // 🔥 old file delete
        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        // new file store
        $yearDirectory = $directory . '/' . date('Y');
        $path = $request->file($inputName)->store($yearDirectory, 'public');

        return $path;
    }
}

if (!function_exists('unlinkSingleFile')) {
    function unlinkSingleFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        // public disk check & delete
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }

        return false;
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

if (!function_exists('normalizePhone')) {
    /**
     *
     * @param string $phone The phone number to normalize.
     * @param string $prefix The prefix to remove (e.g., '+880').
     * @return string The normalized phone number.
     */
    function normalizePhone(string $phone, string $prefix = '+88'): string
    {
        if (strpos($phone, $prefix) === 0) {
            return substr($phone, strlen($prefix));
        }
        return $phone;
    }
}
