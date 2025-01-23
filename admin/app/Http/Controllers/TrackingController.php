<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageDevice;
use App\Models\MessageUrl;
use Illuminate\Http\Response;

/**
 * Tracking controller.
 *
 * @package  App
 * @category Http
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class TrackingController extends Controller
{
    const TRACK_IMAGE = "R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==";

    /**
     * Tracking message is opened.
     *
     * @param  string $idHash
     * @return Response
     */
    public function openImage(string $idHash): Response
    {
        if (ini_get("ignore_user_abort")) {
            ignore_user_abort(true);
        }

        // turn off gzip compression
        if (function_exists("apache_setenv")) {
            apache_setenv("no-gzip", 1);
        }
        ini_set("zlib.output_compression", "0");

        if ($message = Message::firstWhere("hash", $idHash)) {
            $this->trackMessage($message);
            return response(base64_decode(self::TRACK_IMAGE))->withHeaders([
                "Content-Type" => "image/gif",
                "Content-Length" => "43",
                "Cache-Control" =>
                    "private, no-cache, no-cache=Set-Cookie, proxy-revalidate",
                "Expires" => date(DATE_RFC7231, time() - 86400),
                "Last-Modified" => date(DATE_RFC7231, time() - 3600),
                "Pragma" => "no-cache",
            ]);
        }
        return abort(404);
    }

    /**
     * Tracking message is clicked.
     *
     * @param  string $idHash
     * @return Response
     */
    public function clickUrl(string $idHash): Response
    {
        if ($url = MessageUrl::firstWhere("hash", $idHash)) {
            $this->trackMessage($url->message, true);
            return redirect()->away($url->url);
        }
        return abort(404);
    }

    private function trackMessage(Message $message, bool $clicked = false): void
    {
        $request = request();
        $device = new MessageDevice();
        $device->message_id = $message->id;
        $device->user_agent = $request->userAgent();
        $device->ip_address = $request->ip();
        if ($clicked) {
            $device->clicked_at = $message->last_clicked = now();
            $message->click_count++;
        } else {
            $device->opened_at = $message->last_opened = now();
            $message->open_count++;
        }
        $device->save();
        $message->save();
    }
}
