<?php

namespace App\Traits;

use App\Models\Block;
use App\Models\Lop;
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
                    return NamHoc::ofParish($parish_id)
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
                    return Lop::where('status', 1)
                        // ->whereHas('blockRelation', function ($q) use ($namhoc_id) {
                        //     $q->where('schoolyear', $namhoc_id);
                        // })
                        ->where('schoolyear', $namhoc_id)
                        ->when($khoi_id, fn($query) => $query->where('block', $khoi_id))
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

        return Lop::with(['blockRelation', 'schoolYear'])
            ->with('slug')
            ->withCount('students') // Đếm số học sinh
            ->where('status', 1)
            ->where('schoolyear', $namhoc_id)
            ->when($khoi_id, fn($query) => $query->where('block', $khoi_id))
            ->orderBy('name')
            ->get();
    }

    /**
     * ✅ Làm giàu dữ liệu lớp học
     * 
     * @param Lop $lop
     * @return Lop
     */
    private function enrichLopData(Lop $lop): Lop
    {
        // ✅ Generate slug URL
        $lop->slug_url = $this->generateSlugUrl($lop);

        // ✅ Get teacher names
        $lop->teacher_name = $this->getTeacherNames($lop);

        // ✅ Format dates
        if ($lop->start_date_one) {
            $lop->start_date_one_formatted = date('d/m/Y', strtotime($lop->start_date_one));
        }
        if ($lop->end_date_one) {
            $lop->end_date_one_formatted = date('d/m/Y', strtotime($lop->end_date_one));
        }
        if ($lop->start_date_two) {
            $lop->start_date_two_formatted = date('d/m/Y', strtotime($lop->start_date_two));
        }
        if ($lop->end_date_two) {
            $lop->end_date_two_formatted = date('d/m/Y', strtotime($lop->end_date_two));
        }

        return $lop;
    }


    private function generateSlugUrl($lop)
    {
        try {
            if ($lop->relationLoaded('slug') && $lop->slug) {
                $keyword = $lop->slug->keyword;
                $extension = config('settings.url_prefix', '.html');
                return url($keyword . $extension);
            }

            // Fallback
            return route('lop.show', $lop->id);
        } catch (\Exception $e) {
            Log::warning('LopList: Error generating slug URL', [
                'lop_id' => $lop->id,
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

        return Lop::where('status', 1)
            ->where('schoolyear', $namhoc_id)
            ->when($khoi_id, fn($query) => $query->where('block', $khoi_id))
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

        return Lop::where('status', 1)
            ->where('schoolyear', $namhoc_id)
            ->selectRaw('block, COUNT(*) as total')
            ->groupBy('block')
            ->with('blockRelation')
            ->get();
    }
}
