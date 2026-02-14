<table class="table table-bordered text-center">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>الفرع</th>
            <th>المنطقة</th>
            <th>اسم المسجد</th>
            <th>ملاحظات</th>
            <th>إجراء</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mosques as $mosque)
            <tr @if (str_contains(strtolower($mosque->notes), 'هام')) style="background-color:#fff3cd;" @endif>
                <td><input type="checkbox" class="select-mosque" value="{{ $mosque->id }}"></td>
                <td>{{ $mosque->region->branch->name }}</td>
                <td>{{ $mosque->region->name }}</td>
                <td>{{ $mosque->name }}</td>
                <td>{{ $mosque->notes }}</td>
                <td>
                    <button class="btn btn-primary btn-sm edit-btn" data-id="{{ $mosque->id }}"
                        data-name="{{ $mosque->name }}" data-notes="{{ $mosque->notes }}"
                        data-region="{{ $mosque->region_id }}">تعديل</button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $mosque->id }}">حذف</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
