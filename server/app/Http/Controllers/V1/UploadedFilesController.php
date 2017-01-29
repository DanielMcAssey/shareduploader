<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadedFilesController extends Controller {

    public function upload(Request $request) {
        $__FILE_MAX = ini_get('upload_max_filesize');
        $__FILE_MAX_STR_LENGTH = strlen($__FILE_MAX);
        $__FILE_MAX_MEASURE_UNIT = substr($__FILE_MAX, $__FILE_MAX_STR_LENGTH - 1, 1);
        $__FILE_MAX_MEASURE_UNIT = $__FILE_MAX_MEASURE_UNIT == 'K' ? 'kb' : ($__FILE_MAX_MEASURE_UNIT == 'M' ? 'mb' : ($__FILE_MAX_MEASURE_UNIT == 'G' ? 'gb' : 'B'));
        $__FILE_MAX = substr($__FILE_MAX, 0, $__FILE_MAX_STR_LENGTH - 1);
        $__FILE_MAX = intval($__FILE_MAX);
        $__SIZE_UNITS = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB'=> 3, 'TB'=> 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
        $__FILE_MAX_BYTES = ($__FILE_MAX * pow(1024, $__SIZE_UNITS[strtoupper($__FILE_MAX_MEASURE_UNIT)]));

        // Bigger than post_max_size
        if(empty($_FILES) && empty($_POST))
            return response()->json(['file_too_big'], Response::HTTP_BAD_REQUEST);

        try
        {
            $uploadFile = $request->file('uploadFile');

            // No uploaded file
            if(is_null($uploadFile))
                return response()->json(['missing_parameters'], Response::HTTP_BAD_REQUEST);

            // No user linked to API key - SHOULD NEVER HAPPEN
            if(!Auth::check())
                return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

            $new_quota = bcadd(Auth::user()->quota_used, strval($uploadFile->getSize()));
            // User has gone over quota so cant upload
            if(Auth::user()->quota_max != 0 && $new_quota > Auth::user()->quota_max)
                return response()->json(['quota_exceeded'], Response::HTTP_BAD_REQUEST);

            // File isnt valid
            if(!$uploadFile->isValid())
                return response()->json(['file_invalid'], Response::HTTP_BAD_REQUEST);

            // File is bigger than either environment setting or upload_max_filesize
            if($uploadFile->getSize() > env('UPLOAD_MAX_FILE_SIZE') || $uploadFile->getSize() > $__FILE_MAX_BYTES)
                return response()->json(['file_too_big'], Response::HTTP_BAD_REQUEST);

            // File does not have a supported mime type for upload
            if(!in_array($uploadFile->getMimeType(), config('uploader.allowed_mimes')))
                return response()->json(['mime_unsupported'], Response::HTTP_BAD_REQUEST);

            $fileName = $uploadFile->getClientOriginalName();
            $fileExtension = $uploadFile->getClientOriginalExtension();
            $fileMimeType = $uploadFile->getMimeType();

            $generatedFilename = date('Y_m_d-H_i_s').'-'.str_random(12).'-'.Auth::user()->id.'.'.$fileExtension;
            $uploadFile->move(env('UPLOAD_DIRECTORY'), $generatedFilename);

            $dbFile = new UploadedFile;
            $dbFile->user_id = Auth::user()->id;
            $dbFile->original_name = $fileName;
            $dbFile->filename = $generatedFilename;
            $dbFile->extension = $fileExtension;
            $dbFile->mime = $fileMimeType;
            $dbFile->ip = $request->getClientIp();
            $dbFile->save();
            $clipboardURL = env('UPLOAD_STORE_URL').$generatedFilename;

            Auth::user()->quota_used = $new_quota;
            Auth::user()->save();

            return response()->json(['clipboard_url' => $clipboardURL, 'filename' => $generatedFilename], Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            // Used to catch upload_max_filesize error
            // File is bigger than upload_max_filesize
            return response()->json(['unknown_error'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(Request $request, $id)
    {
        if(!Auth::check())
            return response()->json(['no_auth'], Response::HTTP_FORBIDDEN);

        if(is_null($id))
            return response()->json(['missing_parameters'], Response::HTTP_BAD_REQUEST);

        $uploadFile = UploadedFile::find($id);

        if(is_null($uploadFile))
            return response()->json(['file_not_found'], Response::HTTP_NOT_FOUND);

        if(Auth::user()->id != $uploadFile->user_id && !Auth::user()->is_admin)
            return response()->json(['no_auth_to_file'], Response::HTTP_FORBIDDEN);

        if (!Storage::disk('static-local')->has($uploadFile->filename)) {
            $uploadFile->delete();
            return response()->json(['file_missing'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $new_quota = bcsub(Auth::user()->quota_used, Storage::disk('static-local')->size($uploadFile->filename));
        if(floatval($new_quota) < 0) // Prevent negative values
            $new_quota = 0;

        if(!Storage::disk('static-local')->delete($uploadFile->filename))
            return response()->json(['file_not_deleted'], Response::HTTP_INTERNAL_SERVER_ERROR);


        $uploadFile->delete();
        Auth::user()->quota_used = $new_quota;
        Auth::user()->save();
        return response()->json(['file_deleted'], Response::HTTP_OK);
    }

}
