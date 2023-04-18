<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_customization');
    }

    /**
     * Get brand settings
     *
     * @return array
     */
    public function get(Settings $settings)
    {
        return $settings->brand->all();
    }

    /**
     * Update links
     *
     *
     * @throws ValidationException
     */
    public function updateLinks(Request $request)
    {
        $validated = $this->validate($request, [
            'support_url' => 'required|url|max:250',
            'terms_url' => 'required|url|max:250',
            'policy_url' => 'required|url|max:250',
        ]);

        foreach ($validated as $key => $value) {
            settings()->brand->put($key, $value);
        }
    }

    /**
     * Upload logo
     *
     *
     * @throws ValidationException
     */
    public function uploadLogo(Request $request)
    {
        $dimensions = Rule::dimensions()->maxWidth(100)->ratio(1);

        $this->validate($request, [
            'file' => ['required', 'mimetypes:image/png', 'file', 'max:50', $dimensions],
        ]);

        $this->save($request->file('file'), 'logo_url', 'logo');
    }

    /**
     * Upload favicon
     *
     *
     * @throws ValidationException
     */
    public function uploadFavicon(Request $request)
    {
        $dimensions = Rule::dimensions()->maxWidth(32)->ratio(1);

        $this->validate($request, [
            'file' => ['required', 'mimetypes:image/png', 'file', 'max:10', $dimensions],
        ]);

        $this->save($request->file('file'), 'favicon_url', 'favicon');
    }

    /**
     * Save asset
     */
    protected function save(UploadedFile $file, $key, $name)
    {
        $name = "$name.{$file->extension()}";
        $url = savePublicFile($file, 'assets', $name);
        settings()->brand->put($key, url($url));
    }

    /**
     * Get unique hash
     *
     * @return string
     */
    protected function hash()
    {
        return Str::random(5);
    }
}
