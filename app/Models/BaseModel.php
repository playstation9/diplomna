<?php namespace App\Models;


use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * sanitizes the filename and uploads an image
     *
     * @param string $resource - resource to be uploaded to (catalog, user etc.)
     * @param int $id - id of the resource (a dir with this name will be created)
     * @param object $image - intervention/image instance
     *
     * @return string - sanitized new filename
     *         empty string if there is no image passed
     */
    public function uploadImage($resource, $id, $image)
    {
        if (!isset($image)) {
            return '';
        }

        $newFilename = $originalFilename = snake_case($image->getClientOriginalName());
        $fileInfo    = explode('.', $originalFilename);
        $extension   = $fileInfo[count($fileInfo) - 1];
        unset($fileInfo[count($fileInfo) - 1]);
        $filename    = implode('.', $fileInfo);
        $userImageInfo = Config::get('images.' . $resource);

        foreach ($userImageInfo as $dir => $sizes) {
            $path = public_path() . '/uploads/' . $resource . '/' . $dir . '/' . $id;
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $cnt = 0;
            while (file_exists($path . '/' . $newFilename)) {
                $newFilename = $filename . '_' . $cnt . '.' . $extension;
                $cnt++;
            }

            Image::make($image->getRealPath())
                ->fit($sizes['w'], $sizes['h'])
                ->save($path . '/' . $newFilename);
        }

        return $newFilename;
    }
}