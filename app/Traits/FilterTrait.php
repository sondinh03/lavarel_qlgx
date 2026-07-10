<?php

namespace App\Traits;

use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait FilterTrait
{
    /**
     * Cache TTL (Time To Live) - 1 giờ
     */
    private int $cacheTTL = 3600;


    /**
     * Lấy danh sách năm học theo giáo xứ
     * 
     * @param int $parish_id
     * @param int|null $selectedId
     * @return array
     */
    public function getNamHocs(int $parish_id, ?int $selectedId = null): array
    {
        try {
            // ✅ Cache danh sách năm học
            $namHocs = Cache::remember(
                "namhocs:parish:{$parish_id}",
                $this->cacheTTL,
                function () use ($parish_id) {
                    return NamHoc::where('parish_id', $parish_id)
                        ->active()
                        ->orderByDesc('name')
                        ->pluck('name', 'id');
                }
            );

            // Tự động chọn năm học đầu tiên nếu chưa có lựa chọn
            if ($namHocs->count() > 0 && is_null($selectedId)) {
                $selectedId = $namHocs->keys()->first();
            }

            return compact('namHocs', 'selectedId');
        } catch (\Exception $e) {
            Log::error('Error loading năm học', [
                'parish_id' => $parish_id,
                'error' => $e->getMessage()
            ]);
            return ['namHocs' => collect(), 'selectedId' => null];
        }
    }

    /**
     * Lấy danh sách khối theo năm học
     * 
     * @param int $namhoc_id
     * @param int $parish_id
     * @return Collection
     */
    public function getKhois(?int $namhoc_id): Collection
    {
        if (!$namhoc_id) {
            return collect();
        }

        try {
            return Cache::remember(
                "khois:namhoc:{$namhoc_id}",
                $this->cacheTTL,
                function () use ($namhoc_id) {
                    return Block::where('namhoc', $namhoc_id)
                        ->where('status', 1)
                        ->orderBy('weight', 'asc')
                        ->pluck('name', 'id');
                }
            );
        } catch (\Exception $e) {
            Log::error('Error loading khối', [
                'namhoc_id' => $namhoc_id,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Lấy danh sách lớp theo năm học và khối
     * 
     * @param int $namhoc_id
     * @param int|null $khoi_id
     * @return Collection
     */
    public function getLops(?int $namhoc_id, $khoi_id = null): Collection
    {
        if (!$namhoc_id) {
            return collect();
        }

        try {
            $cacheKey = "lops:simple:namhoc:{$namhoc_id}:khoi" . ($khoi_id ?? 'all');

            return Cache::remember(
                $cacheKey,
                $this->cacheTTL,
                function () use ($namhoc_id, $khoi_id) {
                    return CatechismClass::where('is_active', true)
                        ->where('school_year_id', $namhoc_id)
                        ->when($khoi_id, fn($query) => $query->where('grade_level_id', $khoi_id))
                        ->orderBy('name', 'asc')
                        ->pluck('name', 'id');
                }
            );
        } catch (\Exception $e) {
            Log::error('Error loading lớp list', [
                'namhoc_id' => $namhoc_id,
                'khoi_id' => $khoi_id,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Lấy chi tiết đầy đủ của lớp (không chỉ pluck)
     * 
     * @param int $namhoc_id
     * @param int|null $khoi_id
     * @return Collection
     */
    public function getLopsDetailed(?int $namhoc_id, $khoi_id = null): Collection
    {
        if (!$namhoc_id) {
            return collect();
        }

        // try {
        //     $query = Lop::with(['blockRelation', 'slug', 'students' => function ($q) {
        //         $q->where('status', 1);
        //     }])->withCount(['students' => function ($q) {
        //         $q->where('status', 1);
        //     }])
        //         ->where('status', 1)
        //         ->whereHas('blockRelation', function ($q) use ($namhoc_id) {
        //             $q->where('namhoc', $namhoc_id)
        //                 ->where('status', 1);
        //         });

        //     // Filter theo khối nếu có
        //     if ($khoi_id) {
        //         $query->where('block', $khoi_id);
        //     }

        //     $lops = $query->orderBy('weight', 'asc')
        //         ->orderBy('name', 'asc')
        //         ->get();

        //     // ✅ Transform data: thêm slug_url và teacher_name
        //     return $lops->map(function ($lop) {
        //         return $this->enrichLopData($lop);
        //     });
        // } catch (\Exception $e) {
        //     Log::error('Error loading lớp detailed', [
        //         'namhoc_id' => $namhoc_id,
        //         'khoi_id' => $khoi_id,
        //         'error' => $e->getMessage()
        //     ]);
        //     return collect();
        // }

        return CatechismClass::with(['gradeLevel', 'schoolYear'])
            ->withCount('students')
            ->where('is_active', true)
            ->where('school_year_id', $namhoc_id)
            ->when($khoi_id, fn($query) => $query->where('grade_level_id', $khoi_id))
            ->orderBy('name')
            ->get();
    }

    /**
     * ✅ Làm giàu dữ liệu lớp học
     * 
     * @param Lop $lop
     * @return Lop
     */
    private function enrichLopData(CatechismClass $class): CatechismClass
    {
        $class->slug_url = $this->generateSlugUrl($class);

        return $class;
    }

    private function generateSlugUrl(CatechismClass $class)
    {
        try {
            return route('classes.show', $class->id);
        } catch (\Exception $e) {
            Log::warning('FilterTrait: Error generating class URL', [
                'class_id' => $class->id,
                'error' => $e->getMessage()
            ]);

            return '#';
        }
    }

    /**
     * Đếm số lượng lớp theo điều kiện
     * 
     * @param int $namhoc_id
     * @param int|null $khoi_id
     * @return int
     */
    public function countLops($namhoc_id, $khoi_id = null): int
    {
        if (!$namhoc_id) {
            return 0;
        }

        return CatechismClass::where('is_active', true)
            ->where('school_year_id', $namhoc_id)
            ->when($khoi_id, fn($query) => $query->where('grade_level_id', $khoi_id))
            ->count();
    }

    /**
     * Lấy thống kê lớp theo khối
     * 
     * @param int $namhoc_id
     * @return Collection
     */
    public function getClassStatsByBlock($namhoc_id): Collection
    {
        if (!$namhoc_id) {
            return collect();
        }

        return CatechismClass::where('is_active', true)
            ->where('school_year_id', $namhoc_id)
            ->selectRaw('grade_level_id, COUNT(*) as total')
            ->groupBy('grade_level_id')
            ->with('gradeLevel')
            ->get();
    }
}
