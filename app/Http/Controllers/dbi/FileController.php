<?php

namespace App\Http\Controllers\dbi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use AccessLevel;
use Response;
use Request;


class FileController extends Controller {

    // Get alle directories in 'storage'
    public function index () {   
        /*$directories_raw = Storage::allDirectories ();

        foreach ($directories_raw as $directory) {
            if ( !preg_match ('/system/', $directory) )     // JK: exclude certain directories from list
            {
                $directories [] = 'storage/'.$directory;
            }
        }*/

        AccessLevel::abort (10); // Authentification: Access Level Check

        foreach (config ('dbi.files.directories') as $directory) {
            // JK: Ensure directory exists
            if ( Storage::disk ('silo10') -> has(ltrim ($directory, 'storage/'))) {
                $directories [] = $directory;
            }
        }

        return Response::json($directories);
    }

    // Get all files in given directory
    public function browse ($directory) {
        AccessLevel::abort (10); // Authentification: Access Level Check

        $files_raw = Storage::Files($directory);

        foreach ($files_raw as $file) {
            $explode = explode ('/', $file);
            $files[] = [
                'url' => $file, 
                'name' => end($explode)
            ];
        }

        return !isset($files) ? null : Response::json($files);

        /*$files_raw  = Storage::Files ($directory);

        foreach ($files_raw as $file) 
        {
            $directory      = explode ('/', $file);
            $name           = array_pop ($directory);
            $directory      = implode ('/',$directory);
            $extension      = explode('.', $name);
            $extension      = strtolower ( array_pop ($extension));

            // JK: File is image which can be rendered by Browser
            if (in_array ($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg'] )) {
                $src        = 'storage/'.$file;
                $loading    = 'placeholder/placeholder_'.$extension.'.png';
                $fallback   = 'placeholder/placeholder_'.$extension.'.png';
            
            // JK: File is image but cannot be rendered by Browser -> Digilib
            } elseif (in_array ($extension, ['tif', 'tiff'] )) {
                $src        = 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/'.$file.'&dh=250&dw=250'; //'placeholder/placeholder_'.$extension.'.png'; // -> this should be replaced by digilib-link
                $loading    = 'placeholder/placeholder_'.$extension.'.png';
                $fallback   = 'placeholder/placeholder_'.$extension.'.png';
            
            // JK: File is no image, but of known type
            } elseif (in_array ($extension, ['pdf'] )) {
                $src = $loading = $fallback = 'placeholder/placeholder_'.$extension.'.png';

             // JK: File is of unknown type
            } else {
                $src = $loading = $fallback = 'placeholder/placeholder_not_supported.png';
            }

            $files [] = [
                'url'           => 'storage/'.$file,
                'directory'     => 'storage/'.$directory,
                'name'          => $name,
                'extension'     => $extension,
                'src'           => $src,
                'src_loading'   => $loading,
                'src_fallback'  => $fallback
            ];
        }
        
        return !isset ($files) ? null : Response::json ($files);*/
    }

    // Get information about specific file
    public function info ($directory) {
        /*
        AccessLevel::abort (10); // Authentification: Access Level Check
        
        if ( !Storage::disk ('silo10') -> exists ($directory) ) // JK: Ensure file exists
        {
            $info ['error'] = config ('cn_admin_feedback.not_found').' The dataset was linked to a non existing file:'."\n".'"'.$directory.'"'."\n".'It has been either removed or renamed. Please assign another file.';
        }
        else
        {   
            $directory_explode      = explode ('/', $directory);
            $name                   = array_pop ($directory_explode);
            $extension              = explode('.', $name);
            $extension              = strtolower ( array_pop ($extension));
            $loading                = 'ui-elements/imgload.png';
            $fallback               = 'ui-elements/nopreview.png';
            $mimetype               = Storage::disk('silo10')->getMimeType($directory);
            $size                   = Storage::disk('silo10')->size($directory);

            $info = array (
                'url'           => 'storage/'.$directory,
                'directory'     => 'storage/'.implode ('/', $directory_explode),
                'name'          => $name,
                'extension'     => $extension,
                'src'           => 'storage/'.$file,
                'src_loading'   => $loading,
                'src_fallback'  => $fallback,
                'mimetype'      => $mimetype,
                'size'          => $size
            );
        }

        return Response::json ($info);*/
    }

    // Upload file to storage
    public function upload ($directory) {
        AccessLevel::abort(12); // Authentification: Access Level Check

        $request = Request::all();
        
        if (!empty($request['file'])) { // JK: ensure a file was selected
            
            /*$file                   = $request ['file'];
            $file_name_original     = urlencode ($file -> getClientOriginalName () );   // JK: urlencode to ensure filename is readable
            $file_name_explode      = explode ('.', $file_name_original);
            $file_name_extension    = strtolower (array_pop ($file_name_explode) );     // JK: separate extension from the rest
            $file_name_cut          = implode ('.', $file_name_explode).'_01';          // JK: rebuild cut name and add increment
            $file_name              = $file_name_cut.'.'.$file_name_extension;          // JK: rebuild full name*/

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
            
            $files  = Storage::Files($directory);
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

            if ( Storage::putFileAs($directory, $file, $file_name)) {
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
        AccessLevel::abort (12); // Authentification: Access Level Check

        $delete = substr(Request::post()['file'], 8) == 'storage/' ? substr(Request::post()['file'], 8) : Request::post()['file'];

        if (Storage::disk('silo10') -> exists($delete)) // JK: Ensure file exists
        {
            if (Storage::delete($delete)) {
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
