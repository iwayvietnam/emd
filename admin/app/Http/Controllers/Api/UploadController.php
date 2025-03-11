<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * Upload controller.
 *
 * @package  App
 * @category Http
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class UploadController extends Controller
{
    const UPLOAD_DIR = "attachments";

    /**
     * Upload files.
     *
     * @param  Request $request
     * @return JsonResource
     */
    public function upload(Request $request): JsonResource
    {
        $uploadDir = config("emd.api_upload_dir", self::UPLOAD_DIR);
        $storeDir = $uploadDir . "/" . $request->user()->email;
        $files = [];
        foreach ($request->allFiles() as $file) {
            $files[] = $file->hashName();
            Cache::put($file->hashName(), $file->store($storeDir), 3600);
        }
        JsonResource::withoutWrapping();
        return new JsonResource($files);
    }
}
