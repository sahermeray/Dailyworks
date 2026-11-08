<?php

use App\Models\Contract;
use App\Models\DailyWork;
use App\Models\DailyWorkPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('prevents duplicate contract numbers and stores supervisory members', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('contracts.store'), [
            'contract_number' => '100',
            'contract_name' => 'عقد رئيسي',
            'supervisor_name' => 'محمد علي',
            'executing_company' => 'شركة التنفيذ',
            'assignment_date' => '2025-01-15',
            'supervisory_members' => ['سالم', 'خالد'],
        ])
        ->assertRedirect(route('contracts.index'))
        ->assertSessionHasNoErrors();

    $this->actingAs($user)
        ->post(route('contracts.store'), [
            'contract_number' => '100',
            'contract_name' => 'عقد مكرر',
            'supervisor_name' => 'محمد علي',
            'executing_company' => 'شركة التنفيذ',
            'assignment_date' => '2025-01-15',
            'supervisory_members' => ['سالم'],
        ])
        ->assertSessionHasErrors('contract_number');
});

it('calculates cumulative quantities by contract and work type without mixing units', function () {
    $user = User::factory()->create();

    $contractA = Contract::create([
        'contract_number' => '100',
        'contract_name' => 'العقد الأول',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);

    $contractB = Contract::create([
        'contract_number' => '200',
        'contract_name' => 'العقد الثاني',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-12',
    ]);

    DailyWork::create([
        'contract_id' => $contractA->id,
        'work_date' => '2025-02-01',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 100,
        'details' => 'أول يوم',
    ]);

    DailyWork::create([
        'contract_id' => $contractA->id,
        'work_date' => '2025-02-03',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 150,
        'details' => 'ثاني يوم',
    ]);

    DailyWork::create([
        'contract_id' => $contractA->id,
        'work_date' => '2025-02-02',
        'work_type' => 'ترحيل أتربة',
        'unit' => 'متر مكعب',
        'daily_quantity' => 50,
        'details' => 'اتربة',
    ]);

    DailyWork::create([
        'contract_id' => $contractB->id,
        'work_date' => '2025-02-01',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 40,
        'details' => 'عقد آخر',
    ]);

    $this->assertSame(250, $contractA->dailyWorks()->where('work_type', 'قشط الزفت المهترئ')->where('unit', 'متر')->sum('daily_quantity'));
    $this->assertSame(50, $contractA->dailyWorks()->where('work_type', 'ترحيل أتربة')->where('unit', 'متر مكعب')->sum('daily_quantity'));
    $this->assertSame(40, $contractB->dailyWorks()->where('work_type', 'قشط الزفت المهترئ')->where('unit', 'متر')->sum('daily_quantity'));
});

it('renders report cumulative totals without querying once per row', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '150',
        'contract_name' => 'عقد التقرير',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);

    foreach ([
        ['2025-02-01', 'متر', 100],
        ['2025-02-01', 'متر', 10],
        ['2025-02-01', 'متر مكعب', 50],
        ['2025-02-01', null, 25],
        ['2025-02-02', 'متر', 20],
        ['2025-02-02', null, 5],
    ] as [$workDate, $unit, $quantity]) {
        DailyWork::create([
            'contract_id' => $contract->id,
            'work_date' => $workDate,
            'work_type' => 'قشط الزفت المهترئ',
            'unit' => $unit,
            'daily_quantity' => $quantity,
        ]);
    }

    $aggregateQueryCount = 0;
    DB::listen(function ($query) use (&$aggregateQueryCount): void {
        if (str_contains(strtolower($query->sql), 'sum(')) {
            $aggregateQueryCount++;
        }
    });

    $indexResponse = $this->actingAs($user)->get(route('reports.index'));
    $printResponse = $this->actingAs($user)->get(route('reports.print'));

    $indexResponse->assertOk()
        ->assertSee('130.000')
        ->assertSee('210.000')
        ->assertSee('185.000');
    $printResponse->assertOk()
        ->assertSee('130.000')
        ->assertSee('210.000')
        ->assertSee('185.000');

    expect($aggregateQueryCount)->toBeLessThanOrEqual(4);
});

it('loads contract work photos in one query', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '160',
        'contract_name' => 'عقد الصور',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);

    foreach (['first.jpg', 'second.jpg'] as $fileName) {
        $dailyWork = DailyWork::create([
            'contract_id' => $contract->id,
            'work_date' => '2025-02-01',
            'work_type' => 'قشط الزفت المهترئ',
            'unit' => 'متر',
            'daily_quantity' => 10,
        ]);

        DailyWorkPhoto::create([
            'daily_work_id' => $dailyWork->id,
            'photo_path' => 'daily-works/'.$dailyWork->id.'/'.$fileName,
            'original_file_name' => $fileName,
        ]);
    }

    $photoQueryCount = 0;
    DB::listen(function ($query) use (&$photoQueryCount): void {
        if (str_contains(strtolower($query->sql), 'daily_work_photos')) {
            $photoQueryCount++;
        }
    });

    $response = $this->actingAs($user)->get(route('contracts.show', $contract));

    $response->assertOk()
        ->assertSee('first.jpg')
        ->assertSee('second.jpg');
    expect($photoQueryCount)->toBe(1);
});

it('stores uploaded photos in public storage and keeps database records', function () {
    $user = User::factory()->create();
    Storage::fake('public', ['url' => 'http://localhost:8000/storage']);

    $contract = Contract::create([
        'contract_number' => '300',
        'contract_name' => 'العقد الثالث',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-03-15',
    ]);

    $response = $this->actingAs($user)->post(route('daily-works.store'), [
        'contract_id' => $contract->id,
        'work_date' => '2025-03-16',
        'work_type' => 'قشط الزفت المهترئ ٥سم',
        'unit' => 'متر مربع',
        'daily_quantity' => 125.5,
        'details' => 'اختبار رفع صور',
        'photos' => [
            UploadedFile::fake()->image('first-image.jpg', 800, 600),
            UploadedFile::fake()->image('second-image.jpg', 800, 600),
        ],
    ]);

    $response->assertRedirect(route('daily-works.index'));
    $dailyWork = DailyWork::query()->firstOrFail();

    $this->assertDatabaseCount('daily_work_photos', 2);
    $this->assertCount(2, $dailyWork->photos);

    foreach ($dailyWork->photos as $photo) {
        Storage::disk('public')->assertExists($photo->photo_path);
    }

    expect(file_exists(public_path('storage')))->toBeTrue();

    $photo = $dailyWork->photos->firstOrFail();

    $this->get('http://dailyworks.test/daily-works/'.$dailyWork->id)
        ->assertOk()
        ->assertSee('src="http://dailyworks.test/storage/'.$photo->photo_path.'"', false);
});

it('renders the work types with their mapped units on create and edit forms', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '350',
        'contract_name' => 'عقد اختبار الوحدات',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-03-20',
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_date' => '2025-03-21',
        'work_type' => 'قشط الزفت المهترئ ٥سم',
        'unit' => 'متر مربع',
        'daily_quantity' => 10,
    ]);
    $expectedWorkTypeUnits = [
        'قشط الزفت المهترئ ٥سم' => 'متر مربع',
        'قشط الزفت المهترئ ١١سم' => 'متر مربع',
        'تقديم ومد ودحي مجبول اسفلتي اهتراء سماكة ٥ سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي اساس سماكة ٦سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي اساس سماكة ٧سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي بمجابل حرارية' => 'متر مكعب',
        'قص وترقيع بالمجبول الاسفلتي' => 'متر مكعب',
        'تقديم ورش مانع اسفلتي (ام سي زيرو) لزوم طبقة لاصقة' => 'متر مربع',
        'طبقة تشرب(ام سي زيرو)' => 'متر مربع',
        'ترحيل' => 'متر مكعب',
        'تسوية بالمجبول الاسفلتي' => 'متر مكعب',
        'تسوية جوانب الطريق بالكريدر' => 'متر مربع',
        'استبدال طبقات اساس بحص مكسر' => 'متر مكعب',
        'استبدال طبقات ما تحت اساس بحص مكسر' => 'متر مكعب',
        'استبدال وصيانة حواجز بيتونية مكسرة' => 'متر طولي',
    ];

    foreach ([
        route('daily-works.create'),
        route('daily-works.edit', $dailyWork),
    ] as $url) {
        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $response->assertSee('enctype="multipart/form-data" data-turbo="false"', false);
        $document = new DOMDocument;
        $document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        $workTypeOptions = $xpath->query('//select[@name="work_type"]/option[@data-unit]');
        $renderedWorkTypeUnits = [];

        foreach ($workTypeOptions as $option) {
            $renderedWorkTypeUnits[$option->getAttribute('value')] = $option->getAttribute('data-unit');
        }

        expect($renderedWorkTypeUnits)->toBe($expectedWorkTypeUnits);

        $unitOptions = $xpath->query('//select[@name="unit"]/option');
        $renderedUnits = [];

        foreach ($unitOptions as $option) {
            $renderedUnits[] = $option->getAttribute('value');
        }

        expect($renderedUnits)->toBe(['', 'متر', 'متر مربع', 'متر مكعب', 'متر طولي']);
        $response->assertSee('data-auto-select-unit="true"', false);
    }
});

it('accepts linear metres as the unit for broken concrete barrier maintenance', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '360',
        'contract_name' => 'عقد الحواجز',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-03-22',
    ]);

    $this->actingAs($user)
        ->post(route('daily-works.store'), [
            'contract_id' => $contract->id,
            'work_date' => '2025-03-23',
            'work_type' => 'استبدال وصيانة حواجز بيتونية مكسرة',
            'unit' => 'متر طولي',
            'daily_quantity' => 5,
        ])
        ->assertRedirect(route('daily-works.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('daily_works', [
        'contract_id' => $contract->id,
        'work_type' => 'استبدال وصيانة حواجز بيتونية مكسرة',
        'unit' => 'متر طولي',
    ]);
});

it('keeps saved work types editable after they are removed from the new-work options', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '370',
        'contract_name' => 'عقد عمل قديم',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-03-24',
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_date' => '2025-03-25',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 10,
    ]);

    $this->actingAs($user)
        ->put(route('daily-works.update', $dailyWork), [
            'contract_id' => $contract->id,
            'work_date' => '2025-03-26',
            'work_type' => 'قشط الزفت المهترئ',
            'unit' => 'متر',
            'daily_quantity' => 12,
        ])
        ->assertRedirect(route('daily-works.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('daily_works', [
        'id' => $dailyWork->id,
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 12,
    ]);
});

it('uses a normal decimal quantity field without spinner controls', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('daily-works.create'));

    $response->assertOk();
    $response->assertSee('step="any"', false);
    $response->assertSee('inputmode="decimal"', false);
    $response->assertSee('min="0"', false);
    $response->assertSee('type="number"', false);
});

it('keeps the save button inside the edit form when photos are attached', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '400',
        'contract_name' => 'العقد الرابع',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-04-15',
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_date' => '2025-04-16',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 10,
        'details' => 'عمل للاختبار',
    ]);
    DailyWorkPhoto::create([
        'daily_work_id' => $dailyWork->id,
        'photo_path' => 'daily-works/'.$dailyWork->id.'/test.jpg',
        'original_file_name' => 'test.jpg',
    ]);

    $response = $this->actingAs($user)->get(route('daily-works.edit', $dailyWork));

    $response->assertOk();
    $document = new DOMDocument;
    $document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $editForm = $xpath->query('//form[contains(@action, "/daily-works/'.$dailyWork->id.'")]')->item(0);

    expect($editForm)->not->toBeNull();
    expect($xpath->query('.//button[normalize-space(.)="حفظ التعديلات"]', $editForm)->length)->toBe(1);
    expect($xpath->query('.//form', $editForm)->length)->toBe(0);
});

it('updates daily work fields successfully', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '500',
        'contract_name' => 'العقد الخامس',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-05-15',
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_date' => '2025-05-16',
        'work_type' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'daily_quantity' => 10,
        'details' => 'قبل التعديل',
    ]);

    $this->actingAs($user)
        ->put(route('daily-works.update', $dailyWork), [
            'contract_id' => $contract->id,
            'work_date' => '2025-05-17',
            'work_type' => 'ترحيل',
            'unit' => 'متر مكعب',
            'daily_quantity' => 12.5,
            'details' => 'بعد التعديل',
        ])
        ->assertRedirect(route('daily-works.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('daily_works', [
        'id' => $dailyWork->id,
        'work_date' => '2025-05-17',
        'work_type' => 'ترحيل',
        'unit' => 'متر مكعب',
        'daily_quantity' => 12.5,
        'details' => 'بعد التعديل',
    ]);
});
