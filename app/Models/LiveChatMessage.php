<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatMessage extends Model
{
    protected $table = 'live_chat_messages';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'message',
        'attachment_path',
        'attachment_name',
        'is_internal',
        'is_read_by_admin',
        'is_read_by_customer',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_read_by_admin' => 'boolean',
        'is_read_by_customer' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(LiveChat::class, 'conversation_id');
    }
}
