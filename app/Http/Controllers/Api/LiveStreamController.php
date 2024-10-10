<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Token;
use Illuminate\Http\Request;
use Aws\MediaPackage\MediaPackageClient;
use Aws\MediaLive\MediaLiveClient;
use Aws\MediaConnect\MediaConnectClient;
use Illuminate\Support\Facades\Http;
use App\Models\{SteamInfo,Notification};

// require_once('./agora/AccessToken2.php');
class LiveStreamController extends Controller
{

    public function stream_creator(Request $request){
        $request->validate([
            'channel_name'=>'required',
        ]);

        $stream = SteamInfo::create([
            'streamer_id' =>auth()->user()->id,
            'channel_name' =>$request->channel_name,
            'joins'=>0,
        ]);



        return response()->json([
            'status'=>200,
            'stream' =>$stream,
        ]);
    }

    public function stream_joiner(Request $request){
        $request->validate([
            'channel_name' =>'required',
        ]);
        $stream = SteamInfo::where('channel_name',$request->get('channel_name'))->first();

        if($stream){
            $stream->increment('joins');
            return response()->json([
                'status'=>200,
                'message'=>'stream join successful'
            ]);
        }
        else{
            return response()->json([
                'status'=>404,
                'message'=>'no streams available'
            ]);
        }
    }


    public function stream_leaver(Request $request){
        $request->validate([
            'channel_name' =>'required',
        ]);

        $stream = SteamInfo::where('channel_name',$request->get('channel_name'))->first();
        $stream->decrement('joins');
        return response()->json([
            'status'=>200,
            'message'=>'stream left successful'
        ]);
    }

    public function get_stream_data(Request $request){
        $request->validate([
            'channel_name' =>'required',
        ]);

        $stream = SteamInfo::where('channel_name',$request->get('channel_name'))->first();
        if($stream){
            return response()->json([
                'status'=>200,
                'message'=>$stream,
            ]);
        }
        else{
            return response()->json([
                'status'=>404,
                'message'=>'no stream found',
            ]);
        }
    }
    // public function start_live_stream(Request $request)
    // {
    //     $user = auth()->user();
    //     // $client = new MediaPackageClient([
    //     //     'version' => 'latest', // Change to the appropriate version if needed
    //     //     'profile' => 'default', // Use the profile defined in your AWS credentials file
    //     //     'region' => 'us-east-1', // Change to your desired region
    //     //     'http' => [
    //     //         'verify' => false,
    //     //     ],
    //     //     'ssl' => [
    //     //         'verify_peer' => false,
    //     //         'verify_peer_name' => false,
    //     //     ],
    //     // ]);

    //     // $mediapackage = $client->createChannel([
    //     //     'Description' => $user->nick_name.' mediapackage channel',
    //     //     'Id' => preg_replace('/[^a-zA-Z0-9-_]/', '', $user->id . rand(11111, 9999) . now()),
    //     // ]);

    //     // return;
    //     // creating inputs

    //     $client = new MediaLiveClient([
    //         'version' => 'latest', // Change to the appropriate version if needed
    //         'profile' => 'default',
    //         'region' => 'us-east-1', // Change to your desired AWS region
    //         'http' => [
    //                     'verify' => false,
    //                 ],
    //         'ssl' => [
    //             'verify_peer' => false,
    //             'verify_peer_name' => false,
    //         ],
    //     ]);
    //     // $result = $client->createInputSecurityGroup([
    //     //     'WhitelistRules' => [
    //     //         [
    //     //             'Cidr' => '0.0.0.0/0', // The CIDR range to whitelist
    //     //         ],
    //     //     ],
    //     // ]);
    //     // $security_group = $result['SecurityGroup']['Id'];
    //     $result = $client->createInput([
    //         'Destinations' => [
    //             [
    //                 'StreamName' => 'live',
    //             ],
    //             [
    //                 'StreamName' => 'live2',
    //             ]
    //         ],
    //         'InputSecurityGroups' => ['6363906'],
    //         'Name' => auth()->user()->nick_name,
    //         'RequestId' => auth()->user()->id.now(),
    //         'Type' => 'RTMP_PUSH',
    //     ]);

    //     return $result;
    //    // media live channel
    // // Define your request parameters
    // $params = [
    //     'CdiInputSpecification' => [
    //         'Resolution' => 'HD',
    //     ],
    //     'ChannelClass' => 'STANDARD',
    //     'Destinations' => [
    //         [
    //             'Id' => '<string>',
    //             'MediaPackageSettings' => [
    //                 [
    //                     'ChannelId' => '<string>',
    //                 ],
    //             ],
    //             'MultiplexSettings' => [
    //                 'MultiplexId' => '<string>',
    //                 'ProgramName' => '<string>',
    //             ],
    //             'Settings' => [
    //                 [
    //                     'PasswordParam' => '<string>',
    //                     'StreamName' => '<string>',
    //                     'Url' => '<string>',
    //                     'Username' => '<string>',
    //                 ],
    //             ],
    //         ],
    //     ],
    //     'InputSpecification' => [
    //         'Codec' => 'AVC',
    //         'MaximumBitrate' => 'MAX_20_MBPS',
    //         'Resolution' => 'HD',
    //     ],
    //     'InputAttachments' => [
    //         [
    //         'InputAttachmentName' => 'saif',
    //         'InputId' => '1166439',
    //         ]
    //     ],
    //     'Name' => $user->nick_name.' media live channel',
    //     'RequestId' => '<string>',
    //     'Reserved' => '<string>',
    //     'RoleArn' => '<string>',
    // ];

    // // Make the API call
    // $result = $client->createChannel($params);

    // // Handle the response as needed
    // // For example, you can return the result or perform further actions here.
    // return response()->json($result);
    // }

    public function start_live_stream(Request $request){
        // HTTP basic authentication example in PHP using the <Vg k="VSDK" /> Server RESTful API
        // Customer ID
        $customerKey = "7a9cd3733e304fa0af6b86d2435c7b94";
        // Customer secret
        $customerSecret = "d608cc4b00b242ba9e1eb2a4ee27fa03";

        $credentials = $customerKey . ":" . $customerSecret;

        // Encode with base64
        $base64Credentials = base64_encode($credentials);
        // Create authorization header
        $authorizationHeader = "Basic " . $base64Credentials;

        $response = Http::withOptions([
            'verify' => false, // Disable SSL certificate verification
        ])
        ->withHeaders([
            'Authorization' => $authorizationHeader,
            'Content-Type' => 'application/json',
        ])
        ->get('https://api.agora.io/dev/v1/projects', [
            // Any query parameters you might need can be added here
        ]);
        // Check if the request was successful
        if ($response->successful()) {
            return $response->body();
        } else {
            return "Error in HTTP request: " . $response->status();
        }


    }

    public function get_rtc_token(Request $request) {
        $validated = $request->validate([
            'type' => 'required',
            'channel_name'=>'required'
        ]);

        $channelName = $request->channel_name;
        $appID = "c1a173d85c81487ea8ca759a72219a97";
        $appCertificate = "c3160a4c7a8546e99ee718989d5098e9";
        $uid = 0;
        $privilegeExpiredTs = $request->expire_time_in_seconds; // 2 hours

        try{
            $role = null;
            if ($request->type == "PRIVILEGE_PUBLISH_AUDIO_STREAM") {
                $role =  RtcTokenBuilder2::ROLE_PUBLISHER;
            } elseif ($request->type == "PRIVILEGE_PUBLISH_VIDEO_STREAM") {
                $role =  RtcTokenBuilder2::ROLE_PUBLISHER;
            } elseif ($request->type == "PRIVILEGE_JOIN_CHANNEL") {
                $role = RtcTokenBuilder2::ROLE_SUBSCRIBER;
            }

            $token = RtcTokenBuilder2::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

            if (!empty($token)) {
                return [
                    "code" => 0,
                    "token" => $token,
                    "msg" => "success",
                ];
            }

            return ["code" => -1, "token" => "", "msg" => "token error"];
        }
        catch(\Exception $e){
            return response()->json([
                'code'=>500,
                'msg'=>$e->getMessage(),
            ],500);
        }

    }

    public function get_token(){
        return Token::first();
    }

}


class Service
{
    public $type;
    public $privileges;

    public function __construct($serviceType)
    {
        $this->type = $serviceType;
    }

    public function addPrivilege($privilege, $expire)
    {
        $this->privileges[$privilege] = $expire;
    }

    public function getServiceType()
    {
        return $this->type;
    }

    public function pack()
    {
        return Util::packUint16($this->type) . Util::packMapUint32($this->privileges);
    }

    public function unpack(&$data)
    {
        $this->privileges = Util::unpackMapUint32($data);
    }
}

class ServiceRtc extends Service
{
    const SERVICE_TYPE = 1;
    const PRIVILEGE_JOIN_CHANNEL = 1;
    const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    const PRIVILEGE_PUBLISH_DATA_STREAM = 4;
    public $channelName;
    public $uid;

    public function __construct($channelName = "", $uid = "")
    {
        parent::__construct(self::SERVICE_TYPE);
        $this->channelName = $channelName;
        $this->uid = $uid;
    }

    public function pack()
    {
        return parent::pack() . Util::packString($this->channelName) . Util::packString($this->uid);
    }

    public function unpack(&$data)
    {
        parent::unpack($data);
        $this->channelName = Util::unpackString($data);
        $this->uid = Util::unpackString($data);
    }
}

class ServiceRtm extends Service
{
    const SERVICE_TYPE = 2;
    const PRIVILEGE_LOGIN = 1;
    public $userId;

    public function __construct($userId = "")
    {
        parent::__construct(self::SERVICE_TYPE);
        $this->userId = $userId;
    }

    public function pack()
    {
        return parent::pack() . Util::packString($this->userId);
    }

    public function unpack(&$data)
    {
        parent::unpack($data);
        $this->userId = Util::unpackString($data);
    }
}

class ServiceFpa extends Service
{
    const SERVICE_TYPE = 4;
    const PRIVILEGE_LOGIN = 1;

    public function __construct()
    {
        parent::__construct(self::SERVICE_TYPE);
    }

    public function pack()
    {
        return parent::pack();
    }

    public function unpack(&$data)
    {
        parent::unpack($data);
    }
}

class ServiceChat extends Service
{
    const SERVICE_TYPE = 5;
    const PRIVILEGE_USER = 1;
    const PRIVILEGE_APP = 2;
    public $userId;

    public function __construct($userId = "")
    {
        parent::__construct(self::SERVICE_TYPE);
        $this->userId = $userId;
    }

    public function pack()
    {
        return parent::pack() . Util::packString($this->userId);
    }

    public function unpack(&$data)
    {
        parent::unpack($data);
        $this->userId = Util::unpackString($data);
    }
}

class ServiceEducation extends Service
{
    const SERVICE_TYPE = 7;
    const PRIVILEGE_ROOM_USER = 1;
    const PRIVILEGE_USER = 2;
    const PRIVILEGE_APP = 3;

    public $roomUuid;
    public $userUuid;
    public $role;


    public function __construct($roomUuid = "", $userUuid = "", $role = -1)
    {
        parent::__construct(self::SERVICE_TYPE);
        $this->roomUuid = $roomUuid;
        $this->userUuid = $userUuid;
        $this->role = $role;
    }

    public function pack()
    {
        return parent::pack() . Util::packString($this->roomUuid) . Util::packString($this->userUuid) . Util::packInt16($this->role);
    }

    public function unpack(&$data)
    {
        parent::unpack($data);
        $this->roomUuid = Util::unpackString($data);
        $this->userUuid = Util::unpackString($data);
        $this->role = Util::unpackInt16($data);
    }
}

class AccessToken2
{
    const VERSION = "007";
    const VERSION_LENGTH = 3;
    public $appCert;
    public $appId;
    public $expire;
    public $issueTs;
    public $salt;
    public $services = [];

    public function __construct($appId = "", $appCert = "", $expire = 900)
    {
        $this->appId = $appId;
        $this->appCert = $appCert;
        $this->expire = $expire;
        $this->issueTs = time();
        $this->salt = rand(1, 99999999);
    }

    public function addService($service)
    {
        $this->services[$service->getServiceType()] = $service;
    }

    public function build()
    {
        if (!self::isUUid($this->appId) || !self::isUUid($this->appCert)) {
            return "";
        }

        $signing = $this->getSign();
        $data = Util::packString($this->appId) . Util::packUint32($this->issueTs) . Util::packUint32($this->expire)
            . Util::packUint32($this->salt) . Util::packUint16(count($this->services));

        ksort($this->services);
        foreach ($this->services as $key => $service) {
            $data .= $service->pack();
        }

        $signature = hash_hmac("sha256", $data, $signing, true);

        return self::getVersion() . base64_encode(zlib_encode(Util::packString($signature) . $data, ZLIB_ENCODING_DEFLATE));
    }

    public function getSign()
    {
        $hh = hash_hmac("sha256", $this->appCert, Util::packUint32($this->issueTs), true);
        return hash_hmac("sha256", $hh, Util::packUint32($this->salt), true);
    }

    public static function getVersion()
    {
        return self::VERSION;
    }

    public static function isUUid($str)
    {
        if (strlen($str) != 32) {
            return false;
        }
        return ctype_xdigit($str);
    }

    public function parse($token)
    {
        if (substr($token, 0, self::VERSION_LENGTH) != self::getVersion()) {
            return false;
        }

        $data = zlib_decode(base64_decode(substr($token, self::VERSION_LENGTH)));
        $signature = Util::unpackString($data);
        $this->appId = Util::unpackString($data);
        $this->issueTs = Util::unpackUint32($data);
        $this->expire = Util::unpackUint32($data);
        $this->salt = Util::unpackUint32($data);
        $serviceNum = Util::unpackUint16($data);

        $servicesObj = [
            ServiceRtc::SERVICE_TYPE => new ServiceRtc(),
            ServiceRtm::SERVICE_TYPE => new ServiceRtm(),
            ServiceFpa::SERVICE_TYPE => new ServiceFpa(),
            ServiceChat::SERVICE_TYPE => new ServiceChat(),
            ServiceEducation::SERVICE_TYPE => new ServiceEducation(),
        ];
        for ($i = 0; $i < $serviceNum; $i++) {
            $serviceTye = Util::unpackUint16($data);
            $service = $servicesObj[$serviceTye];
            if ($service == null) {
                return false;
            }
            $service->unpack($data);
            $this->services[$serviceTye] = $service;
        }
        return true;
    }
}

class RtcTokenBuilder2
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;

    /**
     * Build the RTC token with uid.
     *
     * @param $appId :          The App ID issued to you by Agora. Apply for a new App ID from
     *                          Agora Dashboard if it is missing from your kit. See Get an App ID.
     * @param $appCertificate : Certificate of the application that you registered in
     *                          the Agora Dashboard. See Get an App Certificate.
     * @param $channelName :    Unique channel name for the AgoraRTC session in the string format
     * @param $uid :            User ID. A 32-bit unsigned integer with a value ranging from 1 to (2^32-1).
     *                          optionalUid must be unique.
     * @param $role :           ROLE_PUBLISHER: A broadcaster/host in a live-broadcast profile.
     *                          ROLE_SUBSCRIBER: An audience(default) in a live-broadcast profile.
     * @param $tokenExpire :    Represented by the number of seconds elapsed since now. If, for example, you want to access the Agora Service within 10 minutes after the token is generated, set $tokenExpire as 600(seconds).
     * @param $privilegeExpire :Represented by the number of seconds elapsed since now. If, for example, you want to enable your privilege for 10 minutes, set $privilegeExpire as 600(seconds).
     * @return The RTC token.
     */
    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $tokenExpire, $privilegeExpire = 0)
    {
        return self::buildTokenWithUserAccount($appId, $appCertificate, $channelName, $uid, $role, $tokenExpire, $privilegeExpire);
    }

    /**
     * Build the RTC token with account.
     *
     * @param $appId :          The App ID issued to you by Agora. Apply for a new App ID from
     *                          Agora Dashboard if it is missing from your kit. See Get an App ID.
     * @param $appCertificate : Certificate of the application that you registered in
     *                          the Agora Dashboard. See Get an App Certificate.
     * @param $channelName :    Unique channel name for the AgoraRTC session in the string format
     * @param $account :        The user's account, max length is 255 Bytes.
     * @param $role :           ROLE_PUBLISHER: A broadcaster/host in a live-broadcast profile.
     *                          ROLE_SUBSCRIBER: An audience(default) in a live-broadcast profile.
     * @param $tokenExpire :    Represented by the number of seconds elapsed since now. If, for example, you want to access the Agora Service within 10 minutes after the token is generated, set $tokenExpire as 600(seconds).
     * @param $privilegeExpire :Represented by the number of seconds elapsed since now. If, for example, you want to enable your privilege for 10 minutes, set $privilegeExpire as 600(seconds).
     * @return The RTC token.
     */
    public static function buildTokenWithUserAccount($appId, $appCertificate, $channelName, $account, $role, $tokenExpire, $privilegeExpire = 0)
    {
        $token = new AccessToken2($appId, $appCertificate, $tokenExpire);
        $serviceRtc = new ServiceRtc($channelName, $account);

        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_JOIN_CHANNEL, $privilegeExpire);
        if ($role == self::ROLE_PUBLISHER) {
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM, $privilegeExpire);
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_VIDEO_STREAM, $privilegeExpire);
            $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_DATA_STREAM, $privilegeExpire);
        }
        // else{
        //     return "incorrect PRIVILEGE specified";
        // }
        // $token->addService($serviceRtc);

        // return response()->json([
        //     'token'=>$token->build(),
        //     'rtc'=>$serviceRtc
        // ]);
        return $token->build();
    }

    /**
     * Generates an RTC token with the specified privilege.
     *
     * This method supports generating a token with the following privileges:
     * - Joining an RTC channel.
     * - Publishing audio in an RTC channel.
     * - Publishing video in an RTC channel.
     * - Publishing data streams in an RTC channel.
     *
     * The privileges for publishing audio, video, and data streams in an RTC channel apply only if you have
     * enabled co-host authentication.
     *
     * A user can have multiple privileges. Each privilege is valid for a maximum of 24 hours.
     * The SDK triggers the onTokenPrivilegeWillExpire and onRequestToken callbacks when the token is about to expire
     * or has expired. The callbacks do not report the specific privilege affected, and you need to maintain
     * the respective timestamp for each privilege in your app logic. After receiving the callback, you need
     * to generate a new token, and then call renewToken to pass the new token to the SDK, or call joinChannel to re-join
     * the channel.
     *
     * @note
     * Agora recommends setting a reasonable timestamp for each privilege according to your scenario.
     * Suppose the expiration timestamp for joining the channel is set earlier than that for publishing audio.
     * When the token for joining the channel expires, the user is immediately kicked off the RTC channel
     * and cannot publish any audio stream, even though the timestamp for publishing audio has not expired.
     *
     * @param $appId The App ID of your Agora project.
     * @param $appCertificate The App Certificate of your Agora project.
     * @param $channelName The unique channel name for the Agora RTC session in string format. The string length must be less than 64 bytes. The channel name may contain the following characters:
     * - All lowercase English letters: a to z.
     * - All uppercase English letters: A to Z.
     * - All numeric characters: 0 to 9.
     * - The space character.
     * - "!", "#", "$", "%", "&", "(", ")", "+", "-", ":", ";", "<", "=", ".", ">", "?", "@", "[", "]", "^", "_", " {", "}", "|", "~", ",".
     * @param $uid The user ID. A 32-bit unsigned integer with a value range from 1 to (2^32 - 1). It must be unique. Set uid as 0, if you do not want to authenticate the user ID, that is, any uid from the app client can join the channel.
     * @param $tokenExpire represented by the number of seconds elapsed since now. If, for example, you want to access the
     * Agora Service within 10 minutes after the token is generated, set tokenExpire as 600(seconds).
     * @param $joinChannelPrivilegeExpire The Unix timestamp when the privilege for joining the channel expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set joinChannelPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes.
     * @param $pubAudioPrivilegeExpire The Unix timestamp when the privilege for publishing audio expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubAudioPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubAudioPrivilegeExpire as the current Unix timestamp.
     * @param $pubVideoPrivilegeExpire The Unix timestamp when the privilege for publishing video expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubVideoPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubVideoPrivilegeExpire as the current Unix timestamp.
     * @param $pubDataStreamPrivilegeExpire The Unix timestamp when the privilege for publishing data streams expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubDataStreamPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubDataStreamPrivilegeExpire as the current Unix timestamp.
     * @return The new Token
     */
    public static function buildTokenWithUidAndPrivilege($appId, $appCertificate, $channelName, $uid,
                                                         $tokenExpire, $joinChannelPrivilegeExpire, $pubAudioPrivilegeExpire,
                                                         $pubVideoPrivilegeExpire, $pubDataStreamPrivilegeExpire)
    {
        return self::buildTokenWithUserAccountAndPrivilege($appId, $appCertificate, $channelName, $uid,
            $tokenExpire, $joinChannelPrivilegeExpire, $pubAudioPrivilegeExpire, $pubVideoPrivilegeExpire, $pubDataStreamPrivilegeExpire);
    }

    /**
     * Generates an RTC token with the specified privilege.
     *
     * This method supports generating a token with the following privileges:
     * - Joining an RTC channel.
     * - Publishing audio in an RTC channel.
     * - Publishing video in an RTC channel.
     * - Publishing data streams in an RTC channel.
     *
     * The privileges for publishing audio, video, and data streams in an RTC channel apply only if you have
     * enabled co-host authentication.
     *
     * A user can have multiple privileges. Each privilege is valid for a maximum of 24 hours.
     * The SDK triggers the onTokenPrivilegeWillExpire and onRequestToken callbacks when the token is about to expire
     * or has expired. The callbacks do not report the specific privilege affected, and you need to maintain
     * the respective timestamp for each privilege in your app logic. After receiving the callback, you need
     * to generate a new token, and then call renewToken to pass the new token to the SDK, or call joinChannel to re-join
     * the channel.
     *
     * @note
     * Agora recommends setting a reasonable timestamp for each privilege according to your scenario.
     * Suppose the expiration timestamp for joining the channel is set earlier than that for publishing audio.
     * When the token for joining the channel expires, the user is immediately kicked off the RTC channel
     * and cannot publish any audio stream, even though the timestamp for publishing audio has not expired.
     *
     * @param $appId The App ID of your Agora project.
     * @param $appCertificate The App Certificate of your Agora project.
     * @param $channelName The unique channel name for the Agora RTC session in string format. The string length must be less than 64 bytes. The channel name may contain the following characters:
     * - All lowercase English letters: a to z.
     * - All uppercase English letters: A to Z.
     * - All numeric characters: 0 to 9.
     * - The space character.
     * - "!", "#", "$", "%", "&", "(", ")", "+", "-", ":", ";", "<", "=", ".", ">", "?", "@", "[", "]", "^", "_", " {", "}", "|", "~", ",".
     * @param $account The user account.
     * @param $tokenExpire represented by the number of seconds elapsed since now. If, for example, you want to access the
     * Agora Service within 10 minutes after the token is generated, set tokenExpire as 600(seconds).
     * @param $joinChannelPrivilegeExpire The Unix timestamp when the privilege for joining the channel expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set joinChannelPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes.
     * @param $pubAudioPrivilegeExpire The Unix timestamp when the privilege for publishing audio expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubAudioPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubAudioPrivilegeExpire as the current Unix timestamp.
     * @param $pubVideoPrivilegeExpire The Unix timestamp when the privilege for publishing video expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubVideoPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubVideoPrivilegeExpire as the current Unix timestamp.
     * @param $pubDataStreamPrivilegeExpire The Unix timestamp when the privilege for publishing data streams expires, represented
     * by the sum of the current timestamp plus the valid time period of the token. For example, if you set pubDataStreamPrivilegeExpire as the
     * current timestamp plus 600 seconds, the token expires in 10 minutes. If you do not want to enable this privilege,
     * set pubDataStreamPrivilegeExpire as the current Unix timestamp.
     * @return The new Token
     */
    public static function buildTokenWithUserAccountAndPrivilege($appId, $appCertificate, $channelName, $account,
                                                                 $tokenExpire, $joinChannelPrivilegeExpire, $pubAudioPrivilegeExpire,
                                                                 $pubVideoPrivilegeExpire, $pubDataStreamPrivilegeExpire)
    {
        $token = new AccessToken2($appId, $appCertificate, $tokenExpire);
        $serviceRtc = new ServiceRtc($channelName, $account);

        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_JOIN_CHANNEL, $joinChannelPrivilegeExpire);
        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM, $pubAudioPrivilegeExpire);
        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_VIDEO_STREAM, $pubVideoPrivilegeExpire);
        $serviceRtc->addPrivilege($serviceRtc::PRIVILEGE_PUBLISH_DATA_STREAM, $pubDataStreamPrivilegeExpire);
        $token->addService($serviceRtc);

        return $token->build();
    }
}

class Util
{
    public static function assertEqual($expected, $actual)
    {
        $debug = debug_backtrace();
        $info = "\n- File:" . basename($debug[1]["file"]) . ", Func:" . $debug[1]["function"] . ", Line:" . $debug[1]["line"];
        if ($expected != $actual) {
            echo $info . "\n  Assert failed" . "\n    Expected :" . $expected . "\n    Actual   :" . $actual;
        } else {
            echo $info . "\n  Assert ok";
        }
    }

    public static function packUint16($x)
    {
        return pack("v", $x);
    }

    public static function unpackUint16(&$data)
    {
        $up = unpack("v", substr($data, 0, 2));
        $data = substr($data, 2);
        return $up[1];
    }

    public static function packUint32($x)
    {
        return pack("V", $x);
    }

    public static function unpackUint32(&$data)
    {
        $up = unpack("V", substr($data, 0, 4));
        $data = substr($data, 4);
        return $up[1];
    }

    public static function packInt16($x)
    {
        return pack("s", $x);
    }

    public static function unpackInt16(&$data)
    {
        $up = unpack("s", substr($data, 0, 2));
        $data = substr($data, 2);
        return $up[1];
    }

    public static function packString($str)
    {
        return self::packUint16(strlen($str)) . $str;
    }

    public static function unpackString(&$data)
    {
        $len = self::unpackUint16($data);
        $up = unpack("C*", substr($data, 0, $len));
        $data = substr($data, $len);
        return implode(array_map("chr", $up));
    }

    public static function packMapUint32($arr)
    {
        ksort($arr);
        $kv = "";
        foreach ($arr as $key => $val) {
            $kv .= self::packUint16($key) . self::packUint32($val);
        }
        return self::packUint16(count($arr)) . $kv;
    }

    public static function unpackMapUint32(&$data)
    {
        $len = self::unpackUint16($data);
        $arr = [];
        for ($i = 0; $i < $len; $i++) {
            $arr[self::unpackUint16($data)] = self::unpackUint32($data);
        }
        return $arr;
    }
}


