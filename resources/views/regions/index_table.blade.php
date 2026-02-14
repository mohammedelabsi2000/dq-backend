@foreach ($regions as $region)
    <tr>
        <td><input type="checkbox" name="regions[]" value="{{ $region->id }}"></td>
        <td>{{ $region->name }}</td>
        <td data-id="{{ $region->branch->id ?? '' }}">{{ $region->branch->name ?? 'غير محدد' }}</td>
        <td>{{ $region->notes }}</td>
        <td>
            <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="{{ $region->id }}">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger delete-btn"
                data-url="{{ route('regions.destroy', $region->id) }}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
@endforeach
