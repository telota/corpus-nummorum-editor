<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use AccessLevel;
use Response;
use Request;
use Auth;


class FileController extends Controller {

    // Get alle directories in 'storage'
    public function index () {

        // Check Auth
        if (Auth::user()->access_level < 10) { die(abort(403)); }

        foreach (config('dbi.files.directories') as $directory) {
            // Ensure directory exists
            if (Storage::disk('data')->has(ltrim($directory, 'storage/'))) {
                $directories[] = $directory;
            }
        }

        return empty($directories) ? abort(404) : Response::json($directories);
    }

    // Get all files in given directory
    public function browse ($directory) {

        if (Auth::user()->access_level < 10 || !in_array('storage/'.$directory, config('dbi.files.directories'))) {
            die(abort(403));
        }
        else if (!Storage::disk('data')->exists($directory)) {
            die(abort(404));
        }
        else  {
            foreach (Storage::disk('data')->Files($directory) as $file) {
                $explode = explode('/', $file);
                $files[] = [
                    'url' => $file,
                    'name' => end($explode)
                ];
            }

            return empty($files) ? null : Response::json($files);
        }
    }

    // Get information about specific file
    public function info ($directory) {
        die(abort(404));
    }

    // Upload file to storage
    public function upload ($directory) {

        // Check Auth
        if (Auth::user()->access_level < 11) { die(abort(403)); }

        $request = Request::all();

        // ensure a file was selected
        if (!empty($request['file'])) {
            $file                   = $request ['file'];
            $file_name_original     = trim($file -> getClientOriginalName());
            $file_name_explode      = explode('.', $file_name_original);
            $file_name_extension    = '.'.strtolower(trim(array_pop($file_name_explode)));  // extract extension
            $file_name_cut          = implode('.', $file_name_explode);                     // implode original name
            $file_name_cut          = strtolower($file_name_cut);                           // set to lower case
            $file_name_cut          = preg_replace("/[[:blank:]]+/", '_', $file_name_cut);  // replace (multiple) blank Spaces
            $file_name_cut          = str_replace('ä', 'ae', $file_name_cut);               // replace "ä"
            $file_name_cut          = str_replace('ö', 'oe', $file_name_cut);               // replace "ö"
            $file_name_cut          = str_replace('ü', 'ue', $file_name_cut);               // replace "ü"
            $file_name_cut          = preg_replace('/[^a-z0-9_]+/', '-', $file_name_cut);   // replace any remaining problematic char
            if (strlen($file_name_cut) < 5) { $file_name_cut = substr('.'.date('U'), -8); } // Replace name with shortened u if name shorther than 5 chars
            $file_name_cut          .= '_01';                                               // add increment
            $file_name              = $file_name_cut.$file_name_extension;                  // rebuild name

            $files  = Storage::disk('data')->Files($directory);
            $i      = 2;

            // Check directory and increase increment if file of this name already exists
            while (in_array($directory.'/'.$file_name, $files)) {
                $file_name_cut = substr($file_name_cut, 0, -2).sprintf('%02s', $i);
                $file_name     = $file_name_cut.$file_name_extension;
                ++$i;

                if ($i > 99) {
                    $feedback['error'] = config('cn_admin_feedback.server_issue').'"'.$file_name.'" could not be uploaded to "storage/'.$directory.'". There are 99 files of the same name. Please rename the file providing an unique name.';
                    die (Response::json($feedback));
                }
            }

            if (Storage::disk('data')->putFileAs($directory, $file, $file_name)) {
                $feedback['success'] = config ('cn_admin_feedback.ok_created').'"'.$file_name.'" has been uploaded to "storage/'.$directory .'".';
                $feedback['url']     = $directory.'/'.$file_name; //'storage/'.$directory.'/'.$file_name;
            }
            else {
                $feedback['error'] = config('cn_admin_feedback.server_issue').'"'.$file_name.'" could not be uploaded to "storage/'.$directory.'". Please contact our IT team.';
            };
        }
        else {
            $feedback['error'] = config('cn_admin_feedback.validation_issue').'no file selected. Please try again.';
        }

        return Response::json($feedback);
    }

    // Delete requested file
    public function delete () {

        // Check Auth
        if (Auth::user()->access_level < 12) { die(abort(403)); }

        $file = Request::post()['file'];

        $delete = substr($file, 8) === 'storage/' ? substr($file, 8) : $file;

        // Ensure file exists
        if (Storage::disk('data')->exists($delete)) {
            if (Storage::disk('data')->delete($delete)) {
                $feedback['success'] = config('cn_admin_feedback.ok_updated').' "storage/'.$delete.'" deleted.';
                $feedback['url']     = 'storage/'.$delete;
            }
            else {
                $feedback['error'] = config('cn_admin_feedback.server_issue').' "storage/'.$delete.'" could not be deleted.';
            }
        }
        else {
            $feedback['error'] = config('cn_admin_feedback.not_found').' "storage/'.$delete.'" could not be found. Someone else might deleted it, already. Please reload and try again.';
        }

        return Response::json($feedback);
    }
}
