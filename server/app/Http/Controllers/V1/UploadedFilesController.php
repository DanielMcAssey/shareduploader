<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RESTActions;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class FilesController extends Controller {

    const MODEL = 'App\Models\File';

    use RESTActions;

    /**
     * Delete File
     *
     * @return Response
     */
    public function delete($id)
    {
        if(Auth::check() && !is_null($id))
        {
            $uploadFile = UploadedFile::find($id);
            if(!is_null($uploadFile) && (Auth::user()->id == $uploadFile->user_id || Auth::user()->is_admin))
            {
                if(\File::exists($uploadFile->location))
                {
                    $new_quota = bcsub(Auth::user()->quota_used, \File::size($uploadFile->location));
                    if(floatval($new_quota) < 0) // Prevent negative values
                    {
                        $new_quota = 0;
                    }
                    if(\File::delete($uploadFile->location))
                    {
                        $uploadFile->delete();
                        Auth::user()->quota_used = $new_quota;
                        Auth::user()->save();
                        return response()->json([], Response::HTTP_OK);
                    }
                    return response()->json([], Response::HTTP_FORBIDDEN);
                }
                $uploadFile->delete();
                return response()->json([], Response::HTTP_FORBIDDEN);
            }
        }
        return response()->json([], Response::HTTP_FORBIDDEN);
    }

}
