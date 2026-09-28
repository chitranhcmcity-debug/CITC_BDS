<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LiveChat extends Model
{
    protected $table = 'hoi_thoai_truc_tuyen';

    public $timestamps = false;

    protected $fillable = [
        'guest_token',
        'ma_nguoi_dung',
        'assigned_staff_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subject',
        'status',
        'priority',
        'last_message',
        'last_message_at',
        'unread_admin',
        'unread_customer',
        'created_at',
        'updated_at',
        'closed_at',
    ];

    protected $casts = [
        'priority' => 'boolean',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'closed_at' => 'datetime',
        'unread_admin' => 'integer',
        'unread_customer' => 'integer',
    ];

    public function getOrCreateConversation(array $identity): object|false
    {
        $userId = $identity['user_id'] ?? null;
        $guestToken = $identity['guest_token'] ?? null;

        if ($userId) {
            $existing = DB::selectOne("
                SELECT * FROM hoi_thoai_truc_tuyen
                WHERE ma_nguoi_dung = ? AND status <> 'closed'
                ORDER BY id DESC LIMIT 1
            ", [$userId]);
        } else {
            $existing = DB::selectOne("
                SELECT * FROM hoi_thoai_truc_tuyen
                WHERE guest_token = ? AND status <> 'closed'
                ORDER BY id DESC LIMIT 1
            ", [$guestToken]);
        }

        if ($existing) {
            return $existing;
        }

        $id = DB::table('hoi_thoai_truc_tuyen')->insertGetId([
            'guest_token' => $guestToken,
            'ma_nguoi_dung' => $userId ? (int) $userId : null,
            'customer_name' => $identity['name'] ?? null,
            'customer_email' => $identity['email'] ?? null,
            'customer_phone' => $identity['phone'] ?? null,
            'status' => 'waiting',
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id ? $this->findConversation((int) $id) : false;
    }

    public function findConversation(int $id): object|false
    {
        $res = DB::selectOne('
            SELECT c.*, nd.ten AS staff_name
            FROM hoi_thoai_truc_tuyen c
            LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id
            WHERE c.id = ?
        ', [$id]);

        return $res ?: false;
    }

    public function findActiveConversation(?int $userId, ?string $guestToken): object|false
    {
        if ($userId) {
            $res = DB::selectOne("
                SELECT * FROM hoi_thoai_truc_tuyen
                WHERE ma_nguoi_dung = ? AND status <> 'closed'
                ORDER BY id DESC LIMIT 1
            ", [$userId]);
        } else {
            $res = DB::selectOne("
                SELECT * FROM hoi_thoai_truc_tuyen
                WHERE guest_token = ? AND status <> 'closed'
                ORDER BY id DESC LIMIT 1
            ", [$guestToken]);
        }

        return $res ?: false;
    }

    public function userCanAccess(object $conversation, ?int $userId, ?string $guestToken): bool
    {
        if ($userId && (int) $conversation->ma_nguoi_dung === (int) $userId) {
            return true;
        }
        if ($guestToken && $conversation->guest_token === $guestToken) {
            return true;
        }

        return false;
    }

    public function addMessage(int $conversationId, string $senderType, ?int $senderId, ?string $senderName, string $message): int|false
    {
        DB::beginTransaction();
        try {
            $msgId = DB::table('tin_nhan_truc_tuyen')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'message' => $message,
                'created_at' => now(),
            ]);

            $updateData = [
                'last_message' => $message,
                'last_message_at' => now(),
                'updated_at' => now(),
            ];

            if (in_array($senderType, ['admin', 'staff'])) {
                $updateData['unread_customer'] = DB::raw('unread_customer + 1');
            } else {
                $updateData['unread_admin'] = DB::raw('unread_admin + 1');
            }

            DB::table('hoi_thoai_truc_tuyen')->where('id', $conversationId)->update($updateData);
            DB::commit();

            return (int) $msgId;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    public function getMessages(int $conversationId, int $afterId = 0): array
    {
        return DB::select('
            SELECT * FROM tin_nhan_truc_tuyen
            WHERE conversation_id = ? AND id > ?
            ORDER BY id ASC
        ', [$conversationId, $afterId]);
    }

    public function listConversations(string $status = 'all'): array
    {
        $query = 'SELECT c.*, nd.ten AS staff_name
                  FROM hoi_thoai_truc_tuyen c
                  LEFT JOIN nguoi_dung nd ON c.assigned_staff_id = nd.id';

        $params = [];
        if ($status !== 'all') {
            $query .= ' WHERE c.status = ?';
            $params[] = $status;
        }

        $query .= ' ORDER BY c.updated_at DESC';

        return DB::select($query, $params);
    }

    public function claimConversation(int $conversationId, int $staffId): bool
    {
        return DB::update("
            UPDATE hoi_thoai_truc_tuyen
            SET assigned_staff_id = ?, status = 'open', updated_at = NOW()
            WHERE id = ?
        ", [$staffId, $conversationId]) >= 0;
    }

    public function closeConversation(int $conversationId): bool
    {
        return DB::update("
            UPDATE hoi_thoai_truc_tuyen
            SET status = 'closed', closed_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ", [$conversationId]) >= 0;
    }

    public function markReadByAdmin(int $conversationId): bool
    {
        return DB::update('
            UPDATE hoi_thoai_truc_tuyen
            SET unread_admin = 0, updated_at = NOW()
            WHERE id = ?
        ', [$conversationId]) >= 0;
    }

    public function markReadByCustomer(int $conversationId): bool
    {
        return DB::update('
            UPDATE hoi_thoai_truc_tuyen
            SET unread_customer = 0, updated_at = NOW()
            WHERE id = ?
        ', [$conversationId]) >= 0;
    }

    public function countWaitingForAdmin(): int
    {
        $res = DB::selectOne("SELECT COUNT(*) as total FROM hoi_thoai_truc_tuyen WHERE status = 'waiting'");

        return $res ? (int) ($res->total ?? 0) : 0;
    }

    public function stats(): array
    {
        $res = DB::selectOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
            FROM hoi_thoai_truc_tuyen
        ");

        return [
            'total' => (int) ($res->total ?? 0),
            'waiting' => (int) ($res->waiting ?? 0),
            'open' => (int) ($res->open ?? 0),
            'closed' => (int) ($res->closed ?? 0),
        ];
    }
}
