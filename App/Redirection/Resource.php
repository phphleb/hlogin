<?php

namespace Phphleb\Hlogin\App\Redirection;

// Routing processing for css-, js-files, images and others

use Hleb\Constructor\Handlers\Request;

class Resource
{
    protected $design;
    protected $type;
    protected $name;
    protected $extension;

    protected $path;

    function get() {
        $this->design = Request::get('des');
        $this->type = $type = Request::get('type');
        $this->name = Request::get('name');
        $this->extension = Request::get('ext');

        if (defined('HLOGIN_LOCALIZE_FRONTEND_DIR') && $this->extension === 'js' &&  strpos($this->name, 'hlogin-lang-') === 0 && file_exists($this->localizePath($directory = HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR .  trim(HLOGIN_LOCALIZE_FRONTEND_DIR, '\\/ ')))) {
            // Found file in another directory.
        } else {
            $directory = HLEB_VENDOR_DIRECTORY . DIRECTORY_SEPARATOR . "phphleb" . DIRECTORY_SEPARATOR . "hlogin" . DIRECTORY_SEPARATOR . "resource" . DIRECTORY_SEPARATOR . $this->design . DIRECTORY_SEPARATOR . $this->type;
        }
        $this->path = realpath($this->localizePath($directory));

        if (!$this->path || strpos($this->path, realpath($directory)) !== 0) {
            return $this->page404();
        }
        $expires = time() + 60 * 60 * 24 * 30 * 6;
        header("Cache-control: max-age=$expires, must-revalidate");
        header("Pragma: public");
        header("Expires: " . gmdate("D, d M Y H:i:s", $expires) . " GMT");
        //  Запрос типа к имени метода
        return $this->$type();
    }

    protected function page404() {
        http_response_code (404);
        return null;
    }

    protected function css() {
        header('Content-type: text/css; charset=utf-8');
        print file_get_contents($this->path);
        exit();
    }

    protected function js() {
        header('Content-Type: application/javascript; charset=utf-8');
        print file_get_contents($this->path);
        exit();
    }

    protected function svg() {
        header('Content-Type: image/svg+xml');
        print file_get_contents($this->path);
        exit();
    }

    protected function images() {
        $mimeType = null;
        switch ($this->extension) {
            case 'bmp':
                $mimeType = "image/bmp";
                break;
            case 'gif':
                $mimeType = "image/gif";
                break;
            case 'ico':
                $mimeType = "image/vnd.microsoft.icon";
                break;
            case 'jpeg':
            case 'jpg':
            default:
                $mimeType = "image/jpeg";
                break;
        }

        header("Content-Type: $mimeType");
        readfile($this->path);
        exit();
    }

    protected function  localizePath(string $directory) {
        return $directory . DIRECTORY_SEPARATOR . $this->name . (empty($this->extension) ? DIRECTORY_SEPARATOR : "." . $this->extension);
    }

}

