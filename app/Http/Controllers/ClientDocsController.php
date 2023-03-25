<?php

namespace App\Http\Controllers;

use App\Models\ClientDocs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class ClientDocsController extends Controller
{


    public function removeChunks(Request $request){
        $files = Storage::disk("chunks")->allFiles();
        foreach ($files as $file) {
            $time = Storage::disk('chunks')->lastModified($file);
            $fileModifiedDateTime = Carbon::parse($time);
            if (Carbon::now()->gt($fileModifiedDateTime->addMinutes(1))) {
                Storage::disk("chunks")->delete($file);
            }
        }
    }


    public function addDocument(Request $request){
        ini_set( 'memory_limit', '-1' );
        ini_set('upload_max_filesize', '2048M');
        ini_set('max_input_time', 360000);
        ini_set('max_execution_time', 360000);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) {}

        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $extension = $file->getClientOriginalExtension();
            $fileName =  md5(time()) . '.' . $extension; // a unique file name

            $disk = Storage::disk('public');
            if($request->file_type == 'VIDEO'){
                $path = $disk->putFileAs('store/courses/videos', $file, $fileName);
            }else if($request->file_type == 'FILE'){
                $path = $disk->putFileAs('clients/docs', $file, $fileName);
            }else if($request->file_type == 'ZIP'){
                $path = $disk->putFileAs('clients/docs', $file, $fileName);
            }else{
                $path = $disk->putFileAs('clients/docs', $file, $fileName);
            }
            // delete chunked file
            unlink($file->getPathname());

            $userid = Auth::user()->id;

            $obj_vid = new ClientDocs();
            $obj_vid->client          = $request->clientid;
            $obj_vid->category        = 'public';
            $obj_vid->doc_type        = ucfirst($request->doctype);
            $obj_vid->description     = $request->description;
            $obj_vid->files           = $path;

            $obj_vid->uploaded   = Carbon::now();
            $obj_vid->created    = $userid;

            $obj_vid->save();

            $message = $request->file_type ." Uploaded";
            return response()->json(['code'=>200, 'status'=>true, 'filename' => $fileName, 'message'=> $message], 200);
        }
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];

    }


    public function downloadfile(Request $request){
        $docid   =  $request->id;
        $document = ClientDocs::where('id', $docid)->first();
        return Storage::disk('public')->download($document->files);
    }



}
