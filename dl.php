<?php ini_set('memory_limit', '1256M');

error_reporting(E_ALL);
ini_set('display_errors', 1);


require('vendor/autoload.php');
require('vendor/simplevk/autoload.php');

use YouTube\YouTubeDownloader;
use DigitalStars\SimpleVK\SimpleVK as vk;


class Dll
{

    private $yt;

    private array $quality = [
        'mp4_144' => 'mp4, video, 144p, audio',
        'mp4_240' => 'mp4, video, 240p, audio',
        'mp4_360' => 'mp4, video, 360p, audio',
        'mp4_480' => 'mp4, video, 480p, audio',
        'mp4_720' => 'mp4, video, 720p, audio',
        'mp4_1080' => 'mp4, video, 1080p, audio',
        'm4a_audio' => 'm4a, audio',

        'webm_360' => 'mp4, video, 360p, audio',
        'webm_480' => 'mp4, video, 480p, audio',
        'webm_720' => 'mp4, video, 720p, audio',
        'webm_1080' => 'mp4, video, 1080p, audio',
        'webm_audio' => 'webm, audio'
    ];

    private array $error_code = [
        0 => 'Unknown error',
        1 => 'Incorrect link or video unavailable',
        2 => 'Failed to download video',
        3 => 'Failed to upload video',
        4 => 'This quality does not exist',
        5 => 'Video in this quality is not available',
        6 => 'Required parameters are missing',
        7 => 'File not found',
        8 => 'Method does not exist',

    ];

    private $dir_for_video = __DIR__ . '/video/';

    public function __construct()
    {
        $this->yt = new YouTubeDownloader();
    }

    protected function downloader(string $direct_url, string $ext)
    {
        $filename = uniqid() . '.' . $ext;

        is_dir($this->dir_for_video) || mkdir($this->dir_for_video);

        $file = file_put_contents($this->dir_for_video . $filename, file_get_contents($direct_url));
        if (isset($file)) {
            return $filename;
        } else return false;
    }

    protected function getExt(string &$format)
    {
        return explode(',', $format)[0];
    }

    protected function error_response(int $code)
    {
        return $this->response_generator([
            'status' => false,
            'error_code' => $code,
            'error_msg' => $this->error_code[$code]
        ]);
    }

    protected function success_response($array)
    {
        return $this->response_generator(array_merge([
            'status' => true,
        ], $array));
    }

    protected function response_generator(array $array)
    {
        return json_encode($array);
    }

    protected function is_json($json)
    {
        json_decode($json);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    protected function parse(string $url, string $format = 'mp4, video, 720p, audio')
    {
        $links = $this->yt->getDownloadLinks($url);

        $links === [] ? $this->error_response(4) : true;

        foreach ($links as $link) {
            if ($link['format'] == $format) {
                return $link['url'];
            }
        }
        return false;
        // var_dump($links);
    }

    protected function download(array &$data, $ext = 'mp4')
    {
        $url = $data['id'];

        $result = $this->downloader($this->parse($url), $ext);
        if ($result) {
            return $this->success_response(['filename' => $result, 'size' => $this->human_filesize(filesize($this->dir_for_video . $result))]);
        } else return $this->error_response(2);
    }

    protected function human_filesize($size, $precision = 2) {
        static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
    }

    protected function checkGetRequest(string $event, array $data)
    {
        switch ($event) {
            case 'download':
                return boolval(isset($data['id']));
                break;

            case 'upload':
                return boolval(isset($data['author'], $data['title'], $data['description'], $data['access_token'], $data['album_id'], $data['group_id'], $data['filename']));
                break;

            case 'direct_link':
                return boolval(isset($data['id']));
                break;

            case 'delete':
                return boolval(isset($data['filename']));
        }
    }

    protected function direct_link(array &$data)
    {
        $id = $data['id'];
        $quality = $data['quality'];

        if (!isset($this->quality[$quality])) {
            return $this->error_response(4);
        } else {
            $result = $this->parse($id, $this->quality[$quality]);
            $result ? $response = $this->success_response(['link' => $result]) : $response = $this->error_response(1);
            return $response;
        }
    }
    protected function upload(array &$data)
    {
        // var_dump($user);
        $video = [
            // 'id' => $data['id'],
            'author' => $data['author'],
            'title' => $data['title'],
            'filename' => $data['filename'],
            'description' => $data['description'],
            'album_id' => $data['album_id']
        ];

        if (file_exists($this->dir_for_video . $video['filename'])) {
            $user = [
                'access_token' => $data['access_token'],
                'group_id' => $data['group_id'],
                'album_id' =>  $data['album_id']
            ];

            $vk = vk::create($user['access_token'], 5.124);

            $load = null;
            try {
                $load = $vk->uploadVideo(
                    [
                        'name' => '[' . $video['author'] . '] ' . $video['title'],
                        'description' => $video['description'],
                        'wallpost' => 0,
                        'group_id' => $user['group_id'],
                        'album_id' => $user['album_id']
                    ],
                    $this->dir_for_video . $video['filename']
                );
            } catch (\Throwable $th) {
                var_dump($th);
                if (is_null($load)) return $this->error_response(3);
            }
            return $this->success_response(['attachment' => $load]);
        } else return $this->error_response(7);
    }

    protected function delete(array &$data)
    {
        $filename = $data['filename'];
        if (file_exists($filename)) {
            unlink($this->dir_for_video . $filename);
            return $this->success_response(['status' => true]);
        } else return $this->success_response(['status' => true]);
    }

    public function run(string $event, array $data)
    {
        if ($this->checkGetRequest($event, $data)) {
            if (method_exists($this, $event)) {
                echo $this->$event($data);
            } else echo $this->error_response(8);
        } else echo $this->error_response(6);
    }
}

