<?php

namespace App\Http\Controllers;

use App\Models\ResourceIamge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ResourceIamgeController extends Controller
{

    protected function getSavedImages(Request $request)
    {
      return ResourceIamge::where('resource_id',$request->id)->get(['id','url']);
    }

    protected function upload(Request $request) {
      if (!$request->hasFile('files')) {
        return response('No files',417);
      }
      $files = $request->file('files'); 
      $response = [];
      foreach($request->file('files') as $key=>$file) {
          $paths = $this->saveImg($file,$key, Auth::id());
          $img = new ResourceIamge();
          $img->resource_id = Auth::user()->resource->id;
          $img->url =  $paths['url'];
          $img->path = $paths['path'];
          $img->save();
      }
      return response('saved',200);
    }

    protected function delete(Request $request)
    {
      $collection = ResourceIamge::whereIn('id',$request->ids)->get();
      $collection->each(function($row) {
        Storage::disk('public')->delete($row->path);
        $row->delete();
      });
    }


    private function saveImg($file,$key,$userId) 
    {
      $path='uploads/' . $userId. '/';
      $file_path=$path . time() . '-' . $key . '.jpg';

      Image::configure(array('driver' => 'gd'));
      $image = Image::make($file);
      
      $image->resize(350,null, function ($constraint) {
          $constraint->aspectRatio();
      })
      ->orientate()
      ->encode('jpg');
      // $image->stripImage();
      $path=\Storage::disk('public')->put( $file_path, (string) $image );

      return ['url' => \Storage::disk('public')->url($file_path), 'path'=> $file_path];
    }
}
