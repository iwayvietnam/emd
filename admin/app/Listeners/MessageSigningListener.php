<?php declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

/**
 * Message signing listener
 * 
 * @package  App
 * @category Listeners
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
class MessageSigningListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSending $event)
    {
        //
    }
}
