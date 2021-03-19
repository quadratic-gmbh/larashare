<?php
/**
 * ImageService.php
 */
namespace App\Services;

use App\Bike;
use App\Image as AppImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Geocoding service - translate location queries into coordinates.
 *
 * @author vadler
 *
 */
class ImageService
{
  
  /**
   * Saves image.
   * 
   * @param Bike $bike
   * @param unknown $file
   */
  public function saveImage(Bike $bike, $file) 
  {
    // create image instance and associate with file
    $path = $file->store('images');
    $app_img = AppImage::create(['filename' => $path]);
    $bike->images()->attach($app_img->id);
    
    // create thumbs    
    $img_sizes = config('image.thumb_sizes');
    $public_disk = Storage::disk('public');    
    foreach($img_sizes as $sz) {
      $img = Image::make($file)->fit($sz);
      $public_disk->put("images/{$sz}/{$app_img->id}.jpg", $img->stream('jpg'));
    }
    
    // create image with 1000px as largest dimension
    $img = Image::make($file);
    $width = 1000;
    $height = 1000;
    $img->height() > $img->width() ? $width = null : $height = null;
    $img->resize($width, $height, function($constraint) {
      $constraint->aspectRatio();
      $constraint->upsize();
    });    
    $public_disk->put("images/1000/{$app_img->id}.jpg", $img->stream('jpg'));
    
    return;
  }
  
  /**
   * Deletes image.
   * 
   * @param Bike $bike
   * @param AppImage $image
   */
  public function deleteImage(Bike $bike, AppImage $image)
  {   
    // delete thumbs and the 1k px image
    $img_sizes = config('image.thumb_sizes');    
    $img_sizes[] = '1000'; 
    $public_disk = Storage::disk('public');
    foreach($img_sizes as $sz) {
      $public_disk->delete("images/{$sz}/{$image->id}.jpg");
    }    
    
    // delete original
    Storage::delete($image->filename);
    
    // detach image from bike
    $bike->images()->detach($image->id);
    
    // delete image entity
    $image->delete();
    
    return;
  }
  
}
