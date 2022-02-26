<?php

namespace HamletCMS\Unsplash\controller;

use HamletCMS\GenericController;
use HamletCMS\HamletCMS;
use HamletCMS\Files\controller\FileSettings;
use rbwebdesigns\core\ImageUpload;

class Unsplash extends GenericController {

    /**
     * Route: /cms/unsplash/search/{BLOG_ID}
     */
    public function showSearch() {
        $this->request->isAjax = true;
        $this->response->setVar('blog', HamletCMS::getActiveBlog());
        $this->response->write('search.tpl', 'Unsplash');
    }

    /**
     * Route: /cms/unsplash/runsearch/{BLOG_ID}
     */
    public function runSearch() {
        $search = $this->request->get('q', '');

        $this->request->isAjax = true;
        $this->response->addHeader('Content-Type', 'application/json');

        if (strlen($search) < 3) {
            $this->response->setBody('{ "error": true, "message": "Search string is not long enough" }');
            $this->response->writeBody();
            return;
        }

        $clientID = HamletCMS::config()['files']['unsplash']['access_key'];

        $queryString = http_build_query([
            'client_id' => $clientID,
            'query' => $search
        ]);
        $requestUri = 'https://api.unsplash.com/search/photos?' . $queryString;
        $fp = fopen($requestUri, 'r');
        
        $photoData = (stream_get_contents($fp)); // unserialize

        $this->response->setBody($photoData);
        $this->response->writeBody();
    }

    /**
     * Route: /cms/unsplash/upload/{BLOG_ID}
     */
    public function uploadFile() {
        $file = $this->request->get('url', '');
        parse_str($file, $fileQuery);

        $this->request->isAjax = true;
        $this->response->addHeader('Content-Type', 'application/json');

        $blog = HamletCMS::getActiveBlog();

        // Upload
        $newFileName = $this->createFileName() . '.' . ($fileQuery['fm'] ?? 'jpg'); // @tdo change core so this is a static function!
        $path = SERVER_PATH_BLOGS . '/'. $blog->id .'/images/'. $newFileName;
        $thumb = str_replace('images/', 'images/sq/', $path);
        $thumb = str_replace(SERVER_PATH_BLOGS . '/'. $blog->id, $blog->resourcePath(), $thumb);

        $save = file_put_contents($path, file_get_contents($file));

        // @todo - create thumbnails!
        $imageDirectory = SERVER_PATH_BLOGS . '/' . $blog->id . '/images';

        $blogConfig = $blog->config();
        $filesConfigExists = array_key_exists('files', $blogConfig) && array_key_exists('imagestyles', $blogConfig['files']);

        if ($filesConfigExists) {
            $sizes = $blogConfig['files']['imagestyles'];
        }
        else {
            $sizes = FileSettings::getDefaultImageSizes();
        }

        $upload = new ImageUpload(['new_path' => $path, 'type' => 'image/' . ($fileQuery['fm'] ?? 'jpg')]);

        // Create thumbnails
        $upload->createThumbnail($imageDirectory . '/xl', null, $sizes['xl']['w'], $sizes['xl']['h']);
        $upload->createThumbnail($imageDirectory . '/l', null, $sizes['l']['w'], $sizes['l']['h']);
        $upload->createThumbnail($imageDirectory . '/m', null, $sizes['m']['w'], $sizes['m']['h']);
        $upload->createThumbnail($imageDirectory . '/s', null, $sizes['s']['w'], $sizes['s']['h']);
        $upload->createThumbnail($imageDirectory . '/xs', null, $sizes['xs']['w'], $sizes['xs']['h']);
        $upload->createThumbnail($imageDirectory . '/sq', null, $sizes['sq']['w'], $sizes['sq']['h']);

        if ($save) {
            $this->response->setBody('{ "error": false, "message": "Image uploaded", "thumb": "' . $thumb .'" }');
            $this->response->writeBody();
        }
        else {
            $this->response->setBody('{ "error": true, "message": "Failed to upload" }');
            $this->response->writeBody();
            return;
        }
    }

    public function createFileName() {
        // @todo check if it already exists
        return rand(10000,32000) . rand(10000,32000);
    }

}
