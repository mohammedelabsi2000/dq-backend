<tr id="row-{{ $mosque->id }}">
    <td><input type="checkbox" class="row-check" value="{{ $mosque->id }}"></td>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $mosque->name }}</td>
    <td>{{ $mosque->region->branch->name }}</td>
    <td>{{ $mosque->region->name }}</td>
    <td>{{ $mosque->notes }}</td>
    <td>
        <button class="btn btn-sm btn-warning" onclick="editMosque({{ $mosque }})">تعديل</button>
        <button class="btn btn-sm btn-danger" onclick="deleteMosque({{ $mosque->id }})">حذف</button>
    </td>
</tr>
