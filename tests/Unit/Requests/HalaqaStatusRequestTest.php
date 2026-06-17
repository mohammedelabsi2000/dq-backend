<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Halaqa\HalaqaStatusRequest;
use App\Models\Halaqa;
use Tests\TestCase;

class HalaqaStatusRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new HalaqaStatusRequest();

        $this->assertFalse($request->authorize());

        $rules = $request->rules();

        $this->assertArrayHasKey('halaqa_id', $rules);
        $this->assertArrayHasKey('from_date', $rules);
        $this->assertContains('required', $rules['halaqa_id']);
        $this->assertContains('exists:halaqas,id', $rules['halaqa_id']);
        $this->assertContains('required', $rules['from_date']);
        $this->assertContains('date', $rules['from_date']);
    }

    /** @test */
    public function it_validates_optional_fields()
    {
        $request = new HalaqaStatusRequest();
        $rules = $request->rules();

        // Check that optional fields exist and have correct rules
        $this->assertArrayHasKey('status_type_id', $rules);
        $this->assertArrayHasKey('sponsorship_type_id', $rules);
        $this->assertArrayHasKey('to_date', $rules);
        $this->assertArrayHasKey('notes', $rules);

        // Check that they are nullable
        $this->assertStringContainsString('nullable', $rules['status_type_id'][0]);
        $this->assertStringContainsString('nullable', $rules['sponsorship_type_id'][0]);
        $this->assertStringContainsString('nullable', $rules['to_date'][0]);
        $this->assertStringContainsString('nullable', $rules['notes']);
    }

    /** @test */
    public function it_validates_to_date_after_from_date()
    {
        $request = new HalaqaStatusRequest();
        $rules = $request->rules();

        $this->assertContains('after_or_equal:from_date', $rules['to_date']);
    }

    /** @test */
    public function it_validates_notes_as_string()
    {
        $request = new HalaqaStatusRequest();
        $rules = $request->rules();

        $this->assertEquals('nullable|string', $rules['notes']);
    }

    /** @test */
    public function it_has_arabic_error_messages()
    {
        $request = new HalaqaStatusRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('halaqa_id.required', $messages);
        $this->assertArrayHasKey('halaqa_id.exists', $messages);
        $this->assertArrayHasKey('status_type_id.in', $messages);
        $this->assertArrayHasKey('sponsorship_type_id.in', $messages);
        $this->assertArrayHasKey('from_date.required', $messages);
        $this->assertArrayHasKey('from_date.date', $messages);
        $this->assertArrayHasKey('to_date.date', $messages);
        $this->assertArrayHasKey('to_date.after_or_equal', $messages);
        $this->assertArrayHasKey('notes.string', $messages);

        $this->assertEquals('يجب اختيار الحلقة.', $messages['halaqa_id.required']);
        $this->assertEquals('الحلقة المحددة غير موجودة.', $messages['halaqa_id.exists']);
        $this->assertEquals('نوع حالة الحلقة غير صالح.', $messages['status_type_id.in']);
        $this->assertEquals(' غير صالح.', $messages['sponsorship_type_id.in']);
        $this->assertEquals('تاريخ البداية مطلوب.', $messages['from_date.required']);
        $this->assertEquals('تاريخ البداية يجب أن يكون تاريخاً صحيحاً.', $messages['from_date.date']);
        $this->assertEquals('تاريخ النهاية يجب أن يكون تاريخ صحيح.', $messages['to_date.date']);
        $this->assertEquals('تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.', $messages['to_date.after_or_equal']);
        $this->assertEquals('الملاحظات يجب أن تكون نصاً.', $messages['notes.string']);
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $this->getValidStatusTypeId(),
            'sponsorship_type_id' => $this->getValidSponsorshipTypeId(),
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_passes_validation_with_minimal_data()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_fails_validation_with_missing_halaqa_id()
    {
        $data = [
            'from_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('halaqa_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_halaqa_id()
    {
        $data = [
            'halaqa_id' => 999,
            'from_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('halaqa_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_missing_from_date()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('from_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_from_date()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => 'invalid-date'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('from_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_to_date()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'to_date' => 'invalid-date'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('to_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_to_date_before_from_date()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-06-01',
            'to_date' => '2024-05-31'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('to_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_status_type_id()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'status_type_id' => 999,
            'from_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status_type_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_sponsorship_type_id()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'sponsorship_type_id' => 999,
            'from_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('sponsorship_type_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_fails_validation_with_invalid_notes()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'notes' => 12345
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    /** @test */
    public function it_passes_validation_with_same_from_and_to_date()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-01-01'
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_passes_validation_with_null_optional_fields()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'status_type_id' => null,
            'sponsorship_type_id' => null,
            'from_date' => '2024-01-01',
            'to_date' => null,
            'notes' => null
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_passes_validation_with_empty_string_notes()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'notes' => ''
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_passes_validation_with_long_notes()
    {
        $halaqa = Halaqa::factory()->create();
        $longNotes = str_repeat('This is a very long note. ', 100);

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'notes' => $longNotes
        ];

        $request = new HalaqaStatusRequest();
        $request->merge($data);

        $validator = validator($data, $request->rules(), $request->messages());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_returns_correct_authorization_status()
    {
        $request = new HalaqaStatusRequest();

        // The authorization depends on HalaqaRequest, so we just test that it returns a boolean
        $this->assertIsBool($request->authorize());
    }

    /**
     * Helper method to get a valid status type ID
     */
    private function getValidStatusTypeId()
    {
        $statusTypes = \App\Helpers\ConstantHelper::getConstantIdsByType('status_type');
        return $statusTypes[0] ?? null;
    }

    /**
     * Helper method to get a valid sponsorship type ID
     */
    private function getValidSponsorshipTypeId()
    {
        $sponsorshipTypes = \App\Helpers\ConstantHelper::getConstantIdsByType('sponsorship_type');
        return $sponsorshipTypes[0] ?? null;
    }
}
