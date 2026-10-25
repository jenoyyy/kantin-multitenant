<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DiningTable;
use App\Models\TableQrToken;
use App\Modules\Admin\Services\QrTokenService;
use App\Support\Tokens\OpaqueToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_stores_only_the_hash_of_the_token(): void
    {
        $table = DiningTable::factory()->create();

        $plain = app(QrTokenService::class)->issue($table);

        $this->assertTrue(
            TableQrToken::where('token_hash', OpaqueToken::hash($plain))->exists()
        );
        $this->assertSame(1, TableQrToken::where('status', 'active')->count());
    }

    public function test_rotate_revokes_old_token_and_keeps_single_active(): void
    {
        $table = DiningTable::factory()->create();
        $service = app(QrTokenService::class);

        $old = $service->issue($table);
        $new = $service->rotate($table);

        $this->assertNotSame($old, $new);
        $this->assertSame(1, TableQrToken::where('dining_table_id', $table->id)->where('status', 'active')->count());
        $this->assertSame(1, TableQrToken::where('dining_table_id', $table->id)->where('status', 'revoked')->whereNotNull('revoked_at')->count());

        $oldRow = TableQrToken::where('token_hash', OpaqueToken::hash($old))->firstOrFail();
        $newRow = TableQrToken::where('token_hash', OpaqueToken::hash($new))->firstOrFail();
        $this->assertFalse($oldRow->isActive());
        $this->assertTrue($newRow->isActive());
    }

    public function test_audit_log_records_issue_without_leaking_the_token(): void
    {
        $table = DiningTable::factory()->create();
        $service = app(QrTokenService::class);

        $plain = $service->issue($table);
        $service->rotate($table);

        $this->assertSame(2, AuditLog::where('action', 'table_qr_token.issued')->count());
        $this->assertStringNotContainsString($plain, json_encode(AuditLog::all()->toArray()));
    }
}