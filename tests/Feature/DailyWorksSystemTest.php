<?php

use App\Models\Contract;
use App\Models\DailyWork;
use App\Models\DailyWorkPhoto;
use App\Models\User;
use App\Models\WorkType;
use App\Services\DailyWorkTotalService;
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

it('creates and edits work types with a measurement unit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('work-types.store'), [
            'name' => 'أعمال اختبارية',
            'unit' => 'm³',
            'is_active' => 1,
        ])
        ->assertRedirect(route('work-types.index'))
        ->assertSessionHasNoErrors();

    $workType = WorkType::query()->where('name', 'أعمال اختبارية')->firstOrFail();
    $this->assertDatabaseHas('work_types', [
        'name' => 'أعمال اختبارية',
        'unit' => 'm³',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->put(route('work-types.update', $workType), [
            'name' => 'أعمال اختبارية معدلة',
            'unit' => 'ton',
            'is_active' => 1,
        ])
        ->assertRedirect(route('work-types.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('work_types', [
        'id' => $workType->id,
        'name' => 'أعمال اختبارية معدلة',
        'unit' => 'ton',
        'is_active' => true,
    ]);
    $this->assertDatabaseHas('work_types', [
        'name' => 'قشط الزفت المهترئ ٥سم',
        'unit' => 'متر مربع',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('work-types.store'), [
            'name' => 'أعمال اختبارية معدلة',
            'unit' => 'm²',
            'is_active' => 1,
        ])
        ->assertSessionHasErrors('name');
});

it('renders dynamic work types and saves the selected type unit instead of submitted unit data', function () {
    $user = User::factory()->create();
    $workType = WorkType::create([
        'name' => 'حفر اختباري',
        'unit' => 'm³',
        'is_active' => true,
    ]);
    $contract = Contract::create([
        'contract_number' => '105',
        'contract_name' => 'عقد الأنواع الديناميكية',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);

    $this->actingAs($user)
        ->withSession(['_old_input' => ['work_type_id' => $workType->id]])
        ->get(route('daily-works.create'))
        ->assertOk()
        ->assertSee('name="work_type_id"', false)
        ->assertSee('value="'.$workType->id.'" data-unit="m³" selected', false)
        ->assertSee('id="unit-create" class="form-control" value="m³" readonly', false);

    $this->actingAs($user)
        ->post(route('daily-works.store'), [
            'contract_id' => $contract->id,
            'work_date' => '2025-02-01',
            'work_type_id' => $workType->id,
            'unit' => 'ton',
            'daily_quantity' => 20,
        ])
        ->assertRedirect(route('daily-works.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('daily_works', [
        'contract_id' => $contract->id,
        'work_type_id' => $workType->id,
        'work_type' => 'حفر اختباري',
        'unit' => 'm³',
        'daily_quantity' => 20,
    ]);
});

it('keeps new work type cumulative report totals separate by type and unit', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'contract_number' => '106',
        'contract_name' => 'عقد الإجماليات',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);
    $excavation = WorkType::create(['name' => 'حفر cumulative', 'unit' => 'm³', 'is_active' => true]);
    $concrete = WorkType::create(['name' => 'خرسانة cumulative', 'unit' => 'm³', 'is_active' => true]);
    $painting = WorkType::create(['name' => 'دهان cumulative', 'unit' => 'm²', 'is_active' => true]);

    foreach ([
        [$excavation, '2025-02-01', 20],
        [$excavation, '2025-02-02', 15],
        [$concrete, '2025-02-01', 8],
        [$painting, '2025-02-01', 100],
        [$painting, '2025-02-02', 50],
    ] as [$workType, $workDate, $quantity]) {
        DailyWork::create([
            'contract_id' => $contract->id,
            'work_type_id' => $workType->id,
            'work_date' => $workDate,
            'work_type' => $workType->name,
            'unit' => $workType->unit,
            'daily_quantity' => $quantity,
        ]);
    }

    $response = $this->actingAs($user)->get(route('reports.index'));

    $response->assertOk()
        ->assertSee('حفر cumulative')
        ->assertSee('خرسانة cumulative')
        ->assertSee('دهان cumulative')
        ->assertSee('35.000')
        ->assertSee('8.000')
        ->assertSee('150.000')
        ->assertSee('m³')
        ->assertSee('m²')
        ->assertSee('value="حفر cumulative"', false);

    $this->actingAs($user)
        ->get(route('reports.print'))
        ->assertOk()
        ->assertSee('حفر cumulative')
        ->assertSee('35.000')
        ->assertSee('150.000');

    $totalService = app(DailyWorkTotalService::class);
    expect($totalService->cumulativeForContract($contract->id, $excavation->name, $excavation->unit, '2025-02-02'))->toBe(35.0);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('حفر cumulative')
        ->assertSee('خرسانة cumulative')
        ->assertSee('دهان cumulative');
});

it('hides deactivated work types from new entries and retains them in historical records', function () {
    $user = User::factory()->create();
    $workType = WorkType::create([
        'name' => 'نوع عمل متوقف',
        'unit' => 'طن',
        'is_active' => false,
    ]);
    $contract = Contract::create([
        'contract_number' => '107',
        'contract_name' => 'عقد نوع متوقف',
        'supervisor_name' => 'المشرف',
        'executing_company' => 'شركة التنفيذ',
        'assignment_date' => '2025-01-10',
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_type_id' => $workType->id,
        'work_date' => '2025-02-01',
        'work_type' => $workType->name,
        'unit' => $workType->unit,
        'daily_quantity' => 10,
    ]);

    $this->actingAs($user)
        ->get(route('daily-works.create'))
        ->assertOk()
        ->assertDontSee('value="'.$workType->id.'" data-unit="طن"', false);

    $this->actingAs($user)
        ->get(route('daily-works.edit', $dailyWork))
        ->assertOk()
        ->assertSee('value="'.$workType->id.'" data-unit="طن" selected', false);

    $this->actingAs($user)
        ->get(route('daily-works.show', $dailyWork))
        ->assertOk()
        ->assertSee('نوع عمل متوقف')
        ->assertSee('طن');

    $this->actingAs($user)
        ->post(route('daily-works.store'), [
            'contract_id' => $contract->id,
            'work_date' => '2025-02-02',
            'work_type_id' => $workType->id,
            'daily_quantity' => 5,
        ])
        ->assertSessionHasErrors('work_type_id');

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('نوع عمل متوقف');

    $this->actingAs($user)
        ->get(route('work-types.index'))
        ->assertOk()
        ->assertSee('نوع عمل متوقف')
        ->assertSee('غير نشط');
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
        'work_type_id' => WorkType::query()->where('name', 'قشط الزفت المهترئ ٥سم')->value('id'),
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
        'work_type_id' => WorkType::query()->where('name', 'قشط الزفت المهترئ ٥سم')->value('id'),
        'work_date' => '2025-03-21',
        'work_type' => 'قشط الزفت المهترئ ٥سم',
        'unit' => 'متر مربع',
        'daily_quantity' => 10,
    ]);
    $expectedWorkTypeUnits = WorkType::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->mapWithKeys(fn (WorkType $workType) => [$workType->name => $workType->unit])
        ->all();

    foreach ([
        [route('daily-works.create'), 'unit-create'],
        [route('daily-works.edit', $dailyWork), 'unit-edit'],
    ] as [$url, $unitInputId]) {
        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $response->assertSee('enctype="multipart/form-data" data-turbo="false"', false);
        $document = new DOMDocument;
        $document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        $workTypeOptions = $xpath->query('//select[@name="work_type_id"]/option[@data-unit]');
        $renderedWorkTypeUnits = [];

        foreach ($workTypeOptions as $option) {
            $renderedWorkTypeUnits[trim($option->textContent)] = $option->getAttribute('data-unit');
        }

        expect($renderedWorkTypeUnits)->toBe($expectedWorkTypeUnits);

        $response->assertSee('id="'.$unitInputId.'"', false)
            ->assertSee('readonly', false);
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
            'work_type_id' => WorkType::query()->where('name', 'استبدال وصيانة حواجز بيتونية مكسرة')->value('id'),
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
    $workType = WorkType::create([
        'name' => 'قشط الزفت المهترئ',
        'unit' => 'متر',
        'is_active' => false,
    ]);
    $dailyWork = DailyWork::create([
        'contract_id' => $contract->id,
        'work_type_id' => $workType->id,
        'work_date' => '2025-03-25',
        'work_type' => $workType->name,
        'unit' => $workType->unit,
        'daily_quantity' => 10,
    ]);

    $this->actingAs($user)
        ->put(route('daily-works.update', $dailyWork), [
            'contract_id' => $contract->id,
            'work_date' => '2025-03-26',
            'work_type_id' => $workType->id,
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
            'work_type_id' => WorkType::query()->where('name', 'ترحيل')->value('id'),
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
