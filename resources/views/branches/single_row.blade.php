<tr>
    <td><input type="checkbox" name="branches[]" value="{{ $branch->id }}"></td>
    <td>{{ $branch->name }}</td>
    <td>{{ $branch->notes }}</td>
    <td>{{ $branch->min_replacement_limit }}</td>
    <td>{{ $branch->max_replacement_limit }}</td>
    <td>
        <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="{{ $branch->id }}">
            <i class="fas fa-edit"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger delete-btn"
            data-url="{{ route('branches.destroy', $branch->id) }}">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
