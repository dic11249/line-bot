<?php

namespace App\Http\Controllers;

use LINE\LINEBot;
use App\Contracts\File;
use Illuminate\Http\Request;
use App\Models\ImageUploadLog;
use Illuminate\Support\Facades\Log;
// use GreatTree\Base\Contracts\Services\File;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\PostbackEvent;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Http\Services\ImageUploadLogService;
use Symfony\Component\HttpFoundation\Response;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\KitchenSink\EventHandler\PostbackEventHandler;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;

class MainController extends Controller
{
    protected $fileService;
    protected $imageUploadLogService;

    public function __construct(File $fileService, ImageUploadLogService $imageUploadLogService)
    {
        $this->fileService = $fileService;
        $this->imageUploadLogService = $imageUploadLogService;
        $this->accessToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $this->channelSecret = env('LINE_CHANNEL_SECRET');
    }

    public function main(Request $request)
    {
        $httpClient = new CurlHTTPClient($this->accessToken);
        $bot = new LINEBot($httpClient, ['channelSecret' => $this->channelSecret]);

        //檢查header
        try {
            $events = $bot->parseEventRequest($request->getContent(), $request->header('X-Line-Signature'));
        } catch (InvalidSignatureException $exception) {
            return response('An exception class that is raised when signature is invalid.', Response::HTTP_FORBIDDEN);
        } catch (InvalidEventRequestException $exception) {
            return response('An exception class that is raised when received invalid event request.', Response::HTTP_FORBIDDEN);
        }

        // LINE BOT回傳資料
        // $events = $request->events;
        Log::info($events);

        foreach ($events as $event) {
            $user_id = $event->getUserId();
            // $message_type = $event->getMessageType();
            $replyToken = $event->getReplyToken();
            Log::info($user_id);
            // Log::info($message_type);
            Log::info($replyToken);


            switch ($event) {
                case ($event instanceof ImageMessage):
                    $message_id = $event->getMessageId();
                    Log::info($message_id);
                    $response = $bot->getMessageContent($message_id);


                    if (!$response->isSucceeded()) {
                        $bot->replyText($replyToken, '操作失敗');
                    }
                    // 查看是否有該使用者的上傳紀錄
                    $uploadLog = ImageUploadLog::where('user_id', $user_id)->orderby('created_at', 'desc')->first();
                    if (!$uploadLog) {
                        //上傳並解析QRCode內容
                        $qr_text = $this->imageUploadLogService->qrCodeUpload($event, $response);
                        // 解析成功則新增一筆Log
                        if ($qr_text) {
                            // 發出postback 詢問使用者是否為XXX
                            $messageAction1 = new PostbackTemplateActionBuilder('確認', $qr_text, '確認驗證正確');
                            $messageAction2 = new MessageTemplateActionBuilder('取消', '確認失敗,請重新上傳');
                            $confirm = new ConfirmTemplateBuilder(
                                '請問您是' . $qr_text . '嗎 ?',
                                [
                                    $messageAction1,
                                    $messageAction2,
                                ]
                            );
                            $template = new TemplateMessageBuilder('請問您是' . $qr_text . '嗎 ?', $confirm);
                            $bot->replyMessage($replyToken, $template);
                        }
                    } else if ($uploadLog->end_time < now()) {
                        //紀錄過期, 重新上傳QRCode
                        $bot->replyText($replyToken, '上傳時間已截止');
                    } else if ($uploadLog && $uploadLog->end_time > now()) {
                        //上傳圖片
                        $this->fileService->saveFile($response, $message_id, $uploadLog, 'image');
                    }

                    break;
                case ($event instanceof TextMessage):
                    $message = $event->getText();

                    if ($message!=='') {
                        $response = $bot->replyText($replyToken, $message);
                    }
                    break;
                case ($event instanceof PostbackEvent):
                    $data = $event->getPostbackData();
                    $this->imageUploadLogService->uploadLogCreate($user_id, $data);
                    $response = $bot->replyText($replyToken, '上傳紀錄已建立, 請開始上傳圖片');
            }
        }
    }

    // public function main(Request $request)
    // {
    //     $httpClient = new CurlHTTPClient($this->accessToken);
    //     $bot = new LINEBot($httpClient, ['channelSecret' => $this->channelSecret]);

    //     //檢查header
    //     try {
    //         $events = $bot->parseEventRequest($request->getContent(), $request->header('X-Line-Signature'));
    //     } catch (InvalidSignatureException $exception) {
    //         return response('An exception class that is raised when signature is invalid.', Response::HTTP_FORBIDDEN);
    //     } catch (InvalidEventRequestException $exception) {
    //         return response('An exception class that is raised when received invalid event request.', Response::HTTP_FORBIDDEN);
    //     }

    //     // LINE BOT回傳資料
    //     // $events = $request->events;
    //     Log::info($events);

    //     foreach ($events as $event) {
    //         // message type
    //         $message_type = $event['message']['type'];
    //         // user id
    //         $user_id = $event['source']['userId'];
    //         // message id
    //         $message_id = $event['message']['id'];
    //         // postback type
    //         $response = $bot->getMessageContent($message_id);

    //         switch ($event['message']['type']) {
    //                 // 圖片
    //             case 'image':
    //                 // 1. 確認最近是否有上傳紀錄
    //                 $uploadLog = ImageUploadLog::where('user_id', $user_id)->orderby('created_at', 'desc')->first();
    //                 // 2-1. 無上傳紀錄 || 有上傳紀錄但已過期 -> 新增上傳紀錄
    //                 if (is_null($uploadLog) || $uploadLog->end_time < now()) {
    //                     // $imageUploadService = new ImageUploadLogService($this->fileService);
    //                     $qr_text = $this->imageUploadLogService->qrCodeUpload($event, $response);

    //                     $messageAction1 = new PostbackTemplateActionBuilder('確認', $qr_text, '確認驗證正確');
    //                     $messageAction2 = new MessageTemplateActionBuilder('取消', '確認失敗,請重新上傳');
    //                     $confirm = new ConfirmTemplateBuilder(
    //                         '請問您是'.$qr_text.'嗎 ?',
    //                         [
    //                             $messageAction1,
    //                             $messageAction2,
    //                         ]);
    //                     $template = new TemplateMessageBuilder('請問您是'.$qr_text.'嗎 ?', $confirm);
    //                     $bot->replyMessage($event['replyToken'], $template);
    //                     // if($qr_text) {
    //                     //     $this->imageUploadLogService->uploadLogCreate($user_id, $qr_text);
    //                     // } else {
    //                     //     $bot->replyText($event['replyToken'], '請上傳QR Code進行認證');
    //                     // }
    //                 } else if ($uploadLog && $uploadLog->end_time > now()) {
    //                     // 2-2. 有上傳紀錄 && 未過期取回紀錄uuid
    //                     $uploadLog = ImageUploadLog::where('user_id', $user_id)->orderby('created_at', 'desc')->first();
    //                     // 3-1. 上傳圖片 若過期擇提示時間已截止
    //                     if ($response->isSucceeded()) {
    //                         $path = $event['source']['userId'] . '/' . $uploadLog->uuid . '/' . $event['message']['id'] . '.png';
    //                         $this->fileService->saveFile($response, $message_id, $uploadLog, 'image');
    //                         // Storage::disk('public')->put($path, $response->getRawBody());
    //                     }
    //                 }
    //                 break;
    //             case 'text':
    //                 switch($event['message']['text']) {
    //                     case '確認失敗,請重新上傳':
    //                         $bot->replyText($event['replyToken'], '紀錄新增失敗, 請重新上傳QR Code');
    //                         break;
    //                     default:
    //                     $bot->replyText($event['replyToken'], '操作失敗, 請重新操作');
    //                 }
    //                 break;
    //             case 'postback':
    //                 $this->imageUploadLogService->uploadLogCreate($user_id, $event['postback']['data']);
    //                 $bot->replyText($event['replyToken'], '作業編號已新增, 請開始上傳圖片');
    //                 break;
    //             default:
    //                 $bot->replyText($event['replyToken'], '窩不知道');
    //         }
    //     }
    // }

    public function test()
    {
        $result = ImageUploadLog::first();
        dd($result->image->last()->getUrl());
    }
}
