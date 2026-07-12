<?php

namespace App\Http\Controllers;

use App\Actions\Parishioner\ExportDonXinRuaToiAction;
use App\Models\Parishioner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerDonXinRuaToiExportController extends Controller
{
    public function __invoke(
        Request $request,
        Parishioner $parishioner,
        ExportDonXinRuaToiAction $action
    ): BinaryFileResponse {
        $this->authorize('view', $parishioner);

        $data = $request->validate([
            'holy_fullname'       => 'required|string|max:200',
            'godparent_name' => 'required|string|max:200',
            'birthday'       => 'required|date',
            'birth_place'    => 'required|string|max:255',
            'birth_order'    => 'required|integer|min:1|max:99',
        ], [
            'holy_fullname.required'       => 'Vui lòng nhập tên thánh, họ tên người được rửa tội',
            'godparent_name.required' => 'Vui lòng nhập tên thánh, họ tên người đỡ đầu',
            'birthday.required'       => 'Vui lòng nhập ngày sinh',
            'birthday.date'           => 'Ngày sinh không hợp lệ',
            'birth_place.required'    => 'Vui lòng nhập nơi sinh',
            'birth_order.required'    => 'Vui lòng nhập con thứ',
            'birth_order.integer'     => 'Con thứ phải là số',
            'birth_order.min'         => 'Con thứ phải từ 1 trở lên',
        ]);

        $result = $action->handle(
            $parishioner,
            $data['holy_fullname'],
            $data['godparent_name'],
            $data['birthday'],
            $data['birth_place'],
            (int) $data['birth_order']
        );

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
