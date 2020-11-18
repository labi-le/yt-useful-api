<?php

namespace DigitalStars\SimpleVK;

trait OtherMethods
{

  public function removeChatUser($chat_id, $member_id)
  {
    if ($this->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $member_id]) == true) {
      return true;
    } else
      return false;
  }

  public function deleteChatPhoto($chat_id)
  {
    return $this->request('messages.deleteChatPhoto', ['chat_id' => $chat_id]);
  }

  public function editChat($chat_id, $title)
  {
    return $this->request('messages.editChat', ['chat_id' => $chat_id, 'title' => $title]);
  }

  public function editMessage($id, $messageID, $params = [])
  {
    if ($id < 1)
      return 0;
    return $this->request('messages.edit', ['message_id' => $messageID, 'peer_id' => $id] + $params);
  }

  public function deleteMessage($id, $delete_for_all = 1)
  {
    return $this->request('messages.delete', ['message_ids' => $id, 'delete_for_all' => $delete_for_all]);
  }

  public function videoAddAlbum($title, $group_id)
  {
    return $this->request('video.addAlbum', ['title' => $title, 'group_id' => $group_id]);
  }

  public function uploadVideo($options = [], $file = false)
  {
    if (!is_array($options)) return false;

    $data_json = $this->request('video.save', $options);

    if (!isset($data_json['upload_url'])) return false;

    $attachment = 'video' . $data_json['owner_id'] . '_' . $data_json['video_id'];
    $upload_url = $data_json['upload_url'];
    $this->sendFiles($upload_url, $file, 'video_file');

    return $attachment;
  }

  public function photosSearch($q, $lat = null, $long = null, $start_time = null, $end_time = null, $sort = null, $offset = null, $count = 50, $radius = null)
  {
    return $this->request(
      'photos.search',
      [
        'q' => $q,
        'lat' => $lat,
        'long' => $long,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'sort' => $sort,
        'offset' => $offset,
        'count' => $count,
        'radius' => $radius
      ]
    );
  }

  public function videoSearch($q, $sort = null, $hd = 1, $adult = 1, $filters = null, $search_own = null, $shorter = null, $longer = null, $count = 20, $offset = null)
  {
    return $this->request(
      'video.search',
      [
        'q' => $q,
        'hd' => $hd,
        'adult' => $adult,
        'longer' => $longer,
        'search_own' => $search_own,
        'sort' => $sort,
        'offset' => $offset,
        'count' => $count,
        'shorter' => $shorter,
        'filters' => $filters
      ]
    );
  }
}
