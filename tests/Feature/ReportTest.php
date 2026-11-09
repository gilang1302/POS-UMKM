<?php

namespace Tests\Feature;

use App\Services\ReportService;
use App\Services\TransactionService;

class ReportTest extends PosTestCase
{
    private function jual($qty)
    {
        return TransactionService::createSale(
            $this->cashier->id, $this->shopA->id,
            [['product_id' => $this->product->id, 'qty' => $qty]], 'cash', 500000
        );
    }

    public function test_ringkasan_laporan_harian(): void
    {
        $this->jual(2);
        $this->jual(1);
        // QRIS yang belum dibayar tidak boleh masuk omset
        TransactionService::createSale($this->cashier->id, $this->shopA->id,
            [['product_id' => $this->product->id, 'qty' => 1]], 'qris', 0);

        $report = ReportService::build('daily', date('Y-m-d'), null);

        $this->assertEquals(2, $report['summary']['count']);
        $this->assertEquals(54000, $report['summary']['total']);
        $this->assertEquals(24000, $report['summary']['cogs']);
        $this->assertEquals(30000, $report['summary']['profit']);
    }

    public function test_ekspor_pdf_dan_excel(): void
    {
        $this->jual(2);

        $pdf = $this->actingAs($this->owner)->get('/reports/pdf?type=daily&date=' . date('Y-m-d'));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $xlsx = $this->actingAs($this->owner)->get('/reports/excel?type=monthly&month=' . date('Y-m'));
        $xlsx->assertOk();
        $this->assertStringContainsString('spreadsheetml', $xlsx->headers->get('Content-Type'));
    }

    public function test_dashboard_data_json(): void
    {
        $this->jual(2);

        $this->actingAs($this->owner)->get('/dashboard/data')
            ->assertOk()
            ->assertJsonStructure(['revenue', 'count', 'days', 'day_revenue', 'latest'])
            ->assertJsonPath('revenue', 36000);
    }
}
