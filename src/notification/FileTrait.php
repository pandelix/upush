<?php


namespace UPush\notification;


use GuzzleHttp\Client;
use UPush\Exceptions\HttpException;
use UPush\Exceptions\InvalidArgumentException;

trait FileTrait
{
    public function uploadContents($content)
    {
        if ($this->data['appkey'] == null) {
            throw new InvalidArgumentException('appkey不能为空!');
        }
        if ($this->data['timestamp'] == null) {
            throw new InvalidArgumentException('timestamp不能为空!');
        }
        if (!is_string($content)) {
            throw new InvalidArgumentException('content应该是一个字符串!');
        }

        $post = array('appkey' => $this->data['appkey'],
            'timestamp' => $this->data['timestamp'],
            'content' => $content
        );
        $url = $this->host . $this->uploadPath;
        $postBody = json_encode($post);
        $sign = md5('POST' . $url . $postBody . $this->appMasterSecret);
        $url = $url . '?sign=' . $sign;
        try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                'body' => $postBody,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $this->data['file_id'] = $data['data']['file_id'];
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode());
        }
    }

    public function getFileId()
    {
        if (array_key_exists('file_id', $this->data)) {
            return $this->data['file_id'];
        }
        return null;
    }
}