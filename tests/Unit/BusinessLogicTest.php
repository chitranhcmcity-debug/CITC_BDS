<?php

namespace Tests\Unit;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessLogicTest extends TestCase
{
    public function test_post_repository_persists_normalized_search_columns(): void
    {
        $repository = new PostRepository;
        $repository->begin();

        try {
            $slug = 'kiem-thu-chuan-hoa-'.bin2hex(random_bytes(6));
            $id = $repository->create([
                'mode' => 'create',
                'category_id' => 1,
                'user_id' => 1,
                'title' => 'Tin kiểm thử chuẩn hóa',
                'slug' => $slug,
                'description' => 'Dữ liệu được rollback sau khi kiểm thử.',
                'price' => 1_250_000_000,
                'area' => 82.5,
                'location' => 'Quận 1, Hồ Chí Minh',
                'province' => 'Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => '',
                'address' => '',
                'property_type' => 'Nhà đất bán',
                'transaction_type' => 'ban',
                'purpose' => 'sale',
                'cover_image' => 'test.webp',
                'status' => 'nhap',
                'vip_level' => 0,
                'expires_at' => now()->addDays(90)->format('Y-m-d H:i:s'),
            ]);

            $this->assertIsInt($id);
            $row = DB::table('du_an')->where('id', $id)->first();
            $this->assertNotNull($row);
            $this->assertSame(1_250_000_000.0, (float) $row->gia_so);
            $this->assertSame(82.5, (float) $row->dien_tich_so);
            $this->assertSame('sale', $row->muc_dich_giao_dich);
            $this->assertSame('nhap', $row->trang_thai);
        } finally {
            $repository->rollBack();
        }
    }

    public function test_payos_webhook_signature_rejects_tampered_data(): void
    {
        require_once base_path('legacy/config/payment.php');

        $data = [
            'orderCode' => 123,
            'amount' => 50_000,
            'code' => '00',
            'desc' => 'Thành công',
        ];
        $payload = [
            'success' => true,
            'code' => '00',
            'data' => $data,
            'signature' => paymentPayosSignature($data),
        ];

        $this->assertTrue(paymentPayosVerifyWebhook($payload));
        $payload['data']['amount']++;
        $this->assertFalse(paymentPayosVerifyWebhook($payload));
    }
}
