<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Message\SendMessage;
use App\Models\Message;
use App\Models\MessageFailure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

/**
 * Email API controller.
 *
 * @package  App
 * @category Http
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class EmailController extends Controller
{
    const QUEUE_NAME = "default";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        JsonResource::withoutWrapping();
    }

    /**
     * List messages.
     *
     * @param  Request $request
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        return JsonResource::collection(
            Message::where([
                "user_id" => $request->user()->id,
            ])->paginate(),
        );
    }

    /**
     * Show a message.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResource
     */
    public function show(Request $request, int $id): JsonResource
    {
        return new JsonResource(
            Message::firstWhere([
                "id" => $id,
                "user_id" => $request->user()->id,
            ]),
        );
    }

    /**
     * Show message devices.
     *
     * @param  Request $request
     * @param  int $id
     * @return JsonResource
     */
    public function devices(Request $request, int $id): JsonResource
    {
        $message = Message::firstWhere([
            "id" => $id,
            "user_id" => $request->user()->id,
        ]);
        return new JsonResource($message?->devices ?? []);
    }

    /**
     * Send a message.
     *
     * @param  Request $request
     * @return JsonResource
     */
    public function send(Request $request): JsonResource
    {
        $request->validate([
            "recipient" => "required",
            "message_id" => "required",
            "subject" => "required",
            "content" => "required",
        ]);

        if (!filter_var($request->recipient, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                "recipient" => ["The recipient is incorrect."],
            ]);
        }

        $uploads = $request->uploads;
        if (is_array($uploads)) {
            $uploads = array_map(
                static fn($upload) => Cache::get($upload),
                $uploads,
            );
        }

        $message = new Message([
            "user_id" => $request->user()->id,
            "from_name" => $request->user()->name,
            "from_email" => $request->user()->email,
            "reply_to" => $request->reply_to ?? $request->user()->email,
            "message_id" => $request->message_id,
            "subject" => $request->subject,
            "content" => $request->content,
            "ip_address" => $request->ip(),
            "recipient" => $request->recipient,
            "headers" => $request->input("headers"),
        ]);
        $message->uploads = $uploads ?? [];
        $message->save();

        $failed = false;
        try {
            $shouldQueue = (bool) config("emd.mail.should_queue", true);
            $trackClick = (bool) config("emd.mail.track_click", false);
            if ($shouldQueue) {
                Mail::to($message->recipient)->queue(
                    new SendMessage($message, $trackClick)->onQueue(
                        config("emd.mail.queue_name", self::QUEUE_NAME),
                    ),
                );
            } else {
                Mail::to($message->recipient)->send(
                    new SendMessage($message, $trackClick),
                );
            }
            $message->sent_at = now();
            $message->save();
        } catch (MailerException $e) {
            logger()::error($e);
            $failed = $e;
        }

        if ($failed) {
            return new JsonResource(
                MessageFailure::create([
                    "message_id" => $message->id,
                    "severity" => __("Send message failed"),
                    "description" => $failed->getMessage(),
                    "failed_at" => now(),
                ]),
            );
        }

        return new JsonResource($message);
    }
}
