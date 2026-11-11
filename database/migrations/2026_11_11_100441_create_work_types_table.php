<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('unit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('daily_works', function (Blueprint $table) {
            $table->foreignId('work_type_id')
                ->nullable()
                ->after('contract_id')
                ->constrained()
                ->restrictOnDelete();
            $table->index('work_type_id');
        });

        $now = now();
        $predefinedWorkTypes = [
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

        foreach ($predefinedWorkTypes as $name => $unit) {
            DB::table('work_types')->insert([
                'name' => $name,
                'unit' => $unit,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $legacyWorkTypes = DB::table('daily_works')
            ->select('work_type', 'unit')
            ->whereNotNull('work_type')
            ->distinct()
            ->orderBy('work_type')
            ->orderBy('unit')
            ->get();

        foreach ($legacyWorkTypes as $legacyWorkType) {
            if (DB::table('work_types')->where('name', $legacyWorkType->work_type)->exists()) {
                continue;
            }

            DB::table('work_types')->insert([
                'name' => $legacyWorkType->work_type,
                'unit' => $legacyWorkType->unit ?: 'غير محدد',
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::statement(
            'UPDATE daily_works SET work_type_id = ('
            .'SELECT work_types.id FROM work_types WHERE work_types.name = daily_works.work_type'
            .') WHERE work_type_id IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('daily_works', function (Blueprint $table) {
            $table->dropIndex(['work_type_id']);
            $table->dropConstrainedForeignId('work_type_id');
        });

        Schema::dropIfExists('work_types');
    }
};
