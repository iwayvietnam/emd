<?php declare(strict_types=1);

namespace App\Mail\Message;

use App\Models\Message;
use App\Models\MessageFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Send message mailable.
 *
 * @package  App
 * @category Mail
 * @author   Nguyen Van Nguyen - nguyennv1981@gmail.com
 */
class SendMessage extends Mailable
{
    use Queueable, SerializesModels;

    const TRACKING_IMG = '<img height="1" width="1" src="{tracking_pixel}" alt="" />';

    /**
     * Create a new message instance.
     *
     * @param  Message $message
     * @param  bool    $trackClick
     * @return void
     */
    public function __construct(
        private readonly Message $message,
        private readonly bool $trackClick = false
    ) {
        foreach ($message->uploads as $upload) {
            $this->attachments[] = [
                "file" => Storage::path($upload),
                "options" => [
                    "mime" => Storage::mimeType($upload),
                ],
            ];
        }
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                $this->message->from_email,
                $this->message->from_name
            ),
            to: $this->message->recipient,
            replyTo: [$this->message->reply_to],
            subject: $this->message->subject
        );
    }

    /**
     * Get the message headers.
     *
     * @return Headers
     */
    public function headers(): Headers
    {
        return new Headers(
            messageId: $this->message->message_id,
            text: $this->message->headers ?? []
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->trackingOpen(
                $this->trackingClick($this->message->content)
            )
        );
    }

    /**
     * Handle failed event.
     *
     * @param  Throwable $e
     * @return void
     */
    public function failed(\Throwable $e): void
    {
        MessageFailure::create([
            "message_id" => $this->message->id,
            "severity" => __("Send message failed"),
            "description" => $e->getMessage(),
            "failed_at" => now(),
        ]);
    }

    private function trackingClick(string $content)
    {
        $searches = $replaces = [];
        if ($this->trackClick) {
            foreach ($this->message->urls() as $url) {
                $searches[] = $url->url;
                $replaces[] = route("tracking_click", ["idHash" => $url->hash]);
            }
        }
        return str_replace($searches, $replaces, $content);
    }

    private function trackingOpen(string $content)
    {
        $trackingImg = str_replace(
            "{tracking_pixel}",
            route("tracking_open", ["idHash" => $this->message->hash]),
            self::TRACKING_IMG
        );
        if (str_contains($content, "</body>")) {
            $content = str_replace(
                "</body>",
                $trackingImg . "</body>",
                $content
            );
        } else {
            $content .= $trackingImg;
        }
        return $content;
    }
}
