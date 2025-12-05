<?php

namespace Tests\Unit;

use App\Models\MeterReadings;
use App\Services\BillingEngine;
use App\Services\BillResult;
use PHPUnit\Framework\TestCase;

class BillingEngineTest extends TestCase
{
    protected BillingEngine $billingEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingEngine = new BillingEngine();
    }

    /**
     * Test VAT calculation.
     */
    public function test_calculate_vat()
    {
        // 15% VAT on R100
        $vat = $this->billingEngine->calculateVat(100, 15);
        $this->assertEquals(15.0, $vat);
        
        // 15% VAT on R190
        $vat = $this->billingEngine->calculateVat(190, 15);
        $this->assertEquals(28.5, $vat);
        
        // 0% VAT
        $vat = $this->billingEngine->calculateVat(100, 0);
        $this->assertEquals(0.0, $vat);
    }

    /**
     * Test VAT calculation with decimal values.
     */
    public function test_calculate_vat_with_decimals()
    {
        $vat = $this->billingEngine->calculateVat(123.45, 15);
        $this->assertEquals(18.52, $vat);
    }

    /**
     * Test BillResult data structure.
     */
    public function test_bill_result_to_array()
    {
        $result = new BillResult(
            consumption: 1000,
            tieredCharge: 100,
            fixedCostsTotal: 50,
            vatAmount: 22.5,
            totalAmount: 172.5,
            isProvisional: false
        );

        $array = $result->toArray();

        $this->assertEquals(1000, $array['consumption']);
        $this->assertEquals(100, $array['tiered_charge']);
        $this->assertEquals(50, $array['fixed_costs_total']);
        $this->assertEquals(22.5, $array['vat_amount']);
        $this->assertEquals(172.5, $array['total_amount']);
        $this->assertFalse($array['is_provisional']);
    }

    /**
     * Test BillResult default values.
     */
    public function test_bill_result_defaults()
    {
        $result = new BillResult();

        $this->assertEquals(0, $result->consumption);
        $this->assertEquals(0, $result->tieredCharge);
        $this->assertEquals(0, $result->fixedCostsTotal);
        $this->assertEquals(0, $result->vatAmount);
        $this->assertEquals(0, $result->totalAmount);
        $this->assertFalse($result->isProvisional);
        $this->assertNull($result->openingReading);
        $this->assertNull($result->closingReading);
        $this->assertEmpty($result->tierBreakdown);
        $this->assertEmpty($result->fixedCostsBreakdown);
    }

    /**
     * Test BillResult with provisional flag.
     */
    public function test_bill_result_provisional()
    {
        $result = new BillResult(
            consumption: 500,
            tieredCharge: 50,
            fixedCostsTotal: 25,
            vatAmount: 11.25,
            totalAmount: 86.25,
            isProvisional: true
        );

        $this->assertTrue($result->isProvisional);
        $this->assertTrue($result->toArray()['is_provisional']);
    }

    /**
     * Test BillResult with tier breakdown.
     */
    public function test_bill_result_with_tier_breakdown()
    {
        $breakdown = [
            ['tier_number' => 1, 'units' => 1000, 'rate' => 10, 'charge' => 10],
            ['tier_number' => 2, 'units' => 500, 'rate' => 20, 'charge' => 10],
        ];

        $result = new BillResult(
            consumption: 1500,
            tieredCharge: 20,
            fixedCostsTotal: 0,
            vatAmount: 3,
            totalAmount: 23,
            isProvisional: false,
            openingReading: null,
            closingReading: null,
            tierBreakdown: $breakdown
        );

        $this->assertCount(2, $result->tierBreakdown);
        $this->assertEquals(1, $result->tierBreakdown[0]['tier_number']);
    }

    /**
     * Test BillingEngine constants.
     */
    public function test_billing_engine_constants()
    {
        $this->assertEquals(15.0, BillingEngine::DEFAULT_VAT_RATE);
        $this->assertEquals(5, BillingEngine::DEFAULT_READ_DAYS_BEFORE);
        $this->assertEquals(20, BillingEngine::DEFAULT_BILLING_DAY);
        $this->assertEquals(1000, BillingEngine::LITERS_TO_KILOLITERS);
    }

    /**
     * Test reading type constants are defined.
     */
    public function test_meter_reading_type_constants()
    {
        $this->assertEquals('ACTUAL', MeterReadings::TYPE_ACTUAL);
        $this->assertEquals('FINAL_ACTUAL', MeterReadings::TYPE_FINAL_ACTUAL);
        $this->assertEquals('ESTIMATED', MeterReadings::TYPE_ESTIMATED);
        $this->assertEquals('FINAL_ESTIMATED', MeterReadings::TYPE_FINAL_ESTIMATED);
    }
}
