<?php
/*
 * Copyright (c) 2022 MynaParrot
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use Mynaparrot\Plugnmeet\Parameters\ChatFeaturesParameters;
use Mynaparrot\Plugnmeet\Parameters\CreateRoomParameters;
use Mynaparrot\Plugnmeet\Parameters\DeleteRecordingParameters;
use Mynaparrot\Plugnmeet\Parameters\EndRoomParameters;
use Mynaparrot\Plugnmeet\Parameters\FetchRecordingsParameters;
use Mynaparrot\Plugnmeet\Parameters\GenerateJoinTokenParameters;
use Mynaparrot\Plugnmeet\Parameters\IsRoomActiveParameters;
use Mynaparrot\Plugnmeet\Parameters\LockSettingsParameters;
use Mynaparrot\Plugnmeet\Parameters\RecordingDownloadTokenParameters;
use Mynaparrot\Plugnmeet\Parameters\RoomFeaturesParameters;
use Mynaparrot\Plugnmeet\Parameters\RoomMetadataParameters;
use Mynaparrot\Plugnmeet\PlugNmeet;

require __DIR__ . "/libs/plugnmeet-sdk-php/vendor/autoload.php";

/**
 *
 */
class plugNmeetConnect
{
    /**
     * @var PlugNmeet
     */
    protected $plugnmeet;

    function __construct($config)
    {
        $this->plugnmeet = new PlugNmeet(
            $config->plugnmeet_server_url,
            $config->plugnmeet_api_key,
            $config->plugnmeet_secret
        );
    }

    public function getUUID()
    {
        return $this->plugnmeet->getUUID();
    }

    /**
     * @param string $roomId
     * @return mixed
     */
    public function isRoomActive(string $roomId)
    {
        $isRoomActiveParameters = new IsRoomActiveParameters();
        $isRoomActiveParameters->setRoomId($roomId);

        return $this->plugnmeet->isRoomActive($isRoomActiveParameters);
    }

    /**
     * @param string $roomId
     * @param string $roomTitle
     * @param string $welcomeMessage
     * @param string $webHookUrl
     * @param array $roomMetadata
     * @return mixed
     */
    public function createRoom(string $roomId, string $roomTitle, string $welcomeMessage, int $max_participants, string $webHookUrl, array $roomMetadata)
    {
        $roomChatFeatures = $roomMetadata['chat_features'];
        $chatFeatures = new ChatFeaturesParameters();
        $chatFeatures->setAllowChat($roomChatFeatures['allow_chat']);
        $chatFeatures->setAllowFileUpload($roomChatFeatures['allow_file_upload']);

        $roomFeatures = $roomMetadata['room_features'];
        $features = new RoomFeaturesParameters();
        $features->setAllowWebcams($roomFeatures['allow_webcams']);
        $features->setMuteOnStart($roomFeatures['mute_on_start']);
        $features->setAllowScreenShare($roomFeatures['allow_screen_share']);
        $features->setAllowRecording($roomFeatures['allow_recording']);
        $features->setAllowRTMP($roomFeatures['allow_rtmp']);
        $features->setAllowViewOtherWebcams($roomFeatures['allow_view_other_webcams']);
        $features->setAllowViewOtherParticipants($roomFeatures['allow_view_other_users_list']);
        $features->setAdminOnlyWebcams($roomFeatures['admin_only_webcams']);
        $features->setChatFeatures($chatFeatures);

        $defaultLocks = $roomMetadata['default_lock_settings'];
        $lockSettings = new LockSettingsParameters();
        $lockSettings->setLockMicrophone($defaultLocks['lock_microphone']);
        $lockSettings->setLockWebcam($defaultLocks['lock_webcam']);
        $lockSettings->setLockScreenSharing($defaultLocks['lock_screen_sharing']);
        $lockSettings->setLockChat($defaultLocks['lock_chat']);
        $lockSettings->setLockChatSendMessage($defaultLocks['lock_chat_send_message']);
        $lockSettings->setLockChatFileShare($defaultLocks['lock_chat_file_share']);

        $metadata = new RoomMetadataParameters();
        $metadata->setRoomTitle($roomTitle);
        $metadata->setWelcomeMessage($welcomeMessage);
        $metadata->setWebhookUrl($webHookUrl);
        $metadata->setFeatures($features);
        $metadata->setDefaultLockSettings($lockSettings);

        $roomCreateParams = new CreateRoomParameters();
        $roomCreateParams->setRoomId($roomId);
        if ($max_participants > 0) {
            $roomCreateParams->setMaxParticipants($max_participants);
        }
        $roomCreateParams->setRoomMetadata($metadata);

        return $this->plugnmeet->createRoom($roomCreateParams);
    }

    /**
     * @param string $roomId
     * @param string $name
     * @param string $userId
     * @param bool $isAdmin
     * @return mixed
     */
    public function getJoinToken(string $roomId, string $name, string $userId, bool $isAdmin)
    {
        $generateJoinTokenParameters = new GenerateJoinTokenParameters();
        $generateJoinTokenParameters->setRoomId($roomId);
        $generateJoinTokenParameters->setName($name);
        $generateJoinTokenParameters->setUserId($userId);
        $generateJoinTokenParameters->setIsAdmin($isAdmin);

        return $this->plugnmeet->getJoinToken($generateJoinTokenParameters);
    }

    public function endRoom(string $roomId)
    {
        $endRoomParameters = new EndRoomParameters();
        $endRoomParameters->setRoomId($roomId);

        return $this->plugnmeet->endRoom($endRoomParameters);
    }

    public function getRecordings(array $roomIds, int $from = 0, int $limit = 20, string $orderBy)
    {
        $fetchRecordingsParameters = new FetchRecordingsParameters();
        $fetchRecordingsParameters->setRoomIds($roomIds);
        $fetchRecordingsParameters->setFrom($from);
        $fetchRecordingsParameters->setLimit($limit);
        $fetchRecordingsParameters->setOrderBy($orderBy);

        return $this->plugnmeet->fetchRecordings($fetchRecordingsParameters);
    }

    public function getRecordingDownloadLink($recordingId)
    {
        $recordingDownloadTokenParameters = new RecordingDownloadTokenParameters();
        $recordingDownloadTokenParameters->setRecordId($recordingId);

        return $this->plugnmeet->getRecordingDownloadToken($recordingDownloadTokenParameters);
    }

    public function deleteRecording($recordingId)
    {
        $deleteRecordingParameters = new DeleteRecordingParameters();
        $deleteRecordingParameters->setRecordId($recordingId);

        return $this->plugnmeet->deleteRecordings($deleteRecordingParameters);
    }
}