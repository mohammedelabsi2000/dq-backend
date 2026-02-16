@extends('layouts.app')
@section('title', 'الفروع')
@section('content')
<<<<<<< HEAD
<div class="container">

    <h2>الفروع</h2>

    <a href="{{ route('branches.create') }}" class="btn btn-primary mb-3">إضافة فرع</a>

    <div class="container">

        <h2 class="page-title mb-4">الفروع</h2>

        <!-- بحث مباشر -->
        <input type="text" id="search" class="form-control mb-3" placeholder="ابحث عن فرع...">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th width="200">التحكم</th>
            </tr>
        </thead>
        <tbody>
        @foreach($branches as $branch)
            <tr>
                <td>{{ $branch->id }}</td>
                <td>{{ $branch->name ?? '-' }}</td>
                <td>
                    <a href="{{ route('branches.show', $branch) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning btn-sm">تعديل</a>

                    <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $branches->links() }}

</div>
=======
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->min_replacement_limit }}</td>
                    <td>{{ $branch->max_replacement_limit }}</td>
                    <td>
                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-primary btn-sm">تعديل</a>

        <!-- جدول الفروع -->
        <form id="branches-form">
            <table class="table table-bordered bg-white shadow-sm rounded">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                        <th>الاسم</th>
                        <th>ملاحظات</th>
                        <th>الحد الأدنى</th>
                        <th>الحد الأعلى</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="branches-table">
                    @include('branches.index_table', ['branches' => $branches])
                </tbody>
            </table>
            <button type="button" id="delete-selected" class="btn btn-danger mt-2"><i class="fas fa-trash"></i> حذف
                المختار</button>
        </form>

        <!-- إضافة فروع متعددة بشكل سلس -->
        <h4 class="mt-5 mb-3">إضافة فروع متعددة</h4>
        <form id="multi-branches-form" class="bg-white p-4 rounded shadow-sm">
            @csrf
            <div id="branches-wrapper">
                <div class="branch-item border rounded p-3 mb-3 shadow-sm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name[]" placeholder="اسم الفرع" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ملاحظات</label>
                            <input type="text" name="notes[]" placeholder="ملاحظات إضافية" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحد الأدنى</label>
                            <input type="number" name="min_replacement_limit[]" placeholder="0" class="form-control"
                                step="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحد الأعلى</label>
                            <input type="number" name="max_replacement_limit[]" placeholder="0" class="form-control"
                                step="1">
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-branch"><i class="fas fa-trash"></i>
                            حذف</button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-info" id="add-branch"><i class="fas fa-plus"></i> إضافة فرع
                    آخر</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ الفروع</button>
            </div>
        </form>

        <!-- تعديل Modal -->
        <div class="modal fade" id="editBranchModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="edit-branch-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">تعديل الفرع</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-branch-id">
                            <div class="mb-3">
                                <label>الاسم</label>
                                <input type="text" id="edit-name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>ملاحظات</label>
                                <textarea id="edit-notes" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>الحد الأدنى</label>
                                <input type="number" id="edit-min" class="form-control" step="1">
                            </div>
                            <div class="mb-3">
                                <label>الحد الأعلى</label>
                                <input type="number" id="edit-max" class="form-control" step="1">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        let csrf = '{{ csrf_token() }}';

        // إضافة فرع جديد
        $('#add-branch').click(function() {
            let newBranch = $('.branch-item:first').clone();
            newBranch.find('input').val('');
            $('#branches-wrapper').append(newBranch);
        });

        // إزالة فرع
        $(document).on('click', '.remove-branch', function() {
            if ($('.branch-item').length > 1) $(this).closest('.branch-item').remove();
        });

        // حفظ فروع متعددة
        $('#multi-branches-form').submit(function(e) {
            e.preventDefault();
            let names = $("input[name='name[]']").map(function() {
                return $(this).val()
            }).get();
            let notes = $("input[name='notes[]']").map(function() {
                return $(this).val()
            }).get();
            let min_replace = $("input[name='min_replacement_limit[]']").map(function() {
                return parseInt($(this).val()) || 0
            }).get();
            let max_replace = $("input[name='max_replacement_limit[]']").map(function() {
                return parseInt($(this).val()) || 0
            }).get();

            for (let i = 0; i < names.length; i++) {
                if (min_replace[i] < 0 || max_replace[i] < 0) {
                    alert("القيم لا يمكن أن تكون سالبة");
                    return;
                }
                if (min_replace[i] > max_replace[i]) {
                    alert("الحد الأدنى لا يمكن أن يكون أكبر من الحد الأعلى");
                    return;
                }
            }

            $.post('{{ route('branches.store') }}', {
                _token: csrf,
                name: names,
                notes: notes,
                min_replacement_limit: min_replace,
                max_replacement_limit: max_replace
            }, function(res) {
                if (res.success) {
                    $('#branches-table').prepend(res.branches);
                    $('#branches-wrapper .branch-item:not(:first)').remove();
                    $('#multi-branches-form')[0].reset();
                }
            });
        });

        // تعديل فرع
        $(document).on('click', '.edit-btn', function() {
            let row = $(this).closest('tr');
            let id = $(this).data('id');
            $('#edit-branch-id').val(id);
            $('#edit-name').val(row.find('td:eq(1)').text());
            $('#edit-notes').val(row.find('td:eq(2)').text());
            $('#edit-min').val(row.find('td:eq(3)').text());
            $('#edit-max').val(row.find('td:eq(4)').text());
            $('#editBranchModal').modal('show');
        });

        // حفظ تعديل
        $('#edit-branch-form').submit(function(e) {
            e.preventDefault();
            let id = $('#edit-branch-id').val();
            let min_val = parseInt($('#edit-min').val()) || 0;
            let max_val = parseInt($('#edit-max').val()) || 0;

            if (min_val < 0 || max_val < 0) {
                alert("القيم لا يمكن أن تكون سالبة");
                return;
            }
            if (min_val > max_val) {
                alert("الحد الأدنى لا يمكن أن يكون أكبر من الحد الأعلى");
                return;
            }

            $.ajax({
                url: '/branches/' + id,
                type: 'PUT',
                data: {
                    _token: csrf,
                    name: $('#edit-name').val(),
                    notes: $('#edit-notes').val(),
                    min_replacement_limit: min_val,
                    max_replacement_limit: max_val
                },
                success: function(res) {
                    if (res.success) {
                        $('input[value="' + id + '"]').closest('tr').replaceWith(res.branch);
                        $('#editBranchModal').modal('hide');
                    }
                }
            });
        });

        // حذف فردي
        $(document).on('click', '.delete-btn', function() {
            let row = $(this).closest('tr');
            if (!confirm('هل أنت متأكد؟')) return;
            $.ajax({
                url: $(this).data('url'),
                type: 'DELETE',
                data: {
                    _token: csrf
                },
                success: function(res) {
                    if (res.success) row.remove();
                }
            });
        });

        // حذف متعدد
        $('#delete-selected').click(function() {
            let ids = $('input[name="branches[]"]:checked').map(function() {
                return this.value
            }).get();
            if (ids.length === 0) {
                alert('اختر فروع');
                return;
            }
            if (!confirm('هل أنت متأكد؟')) return;
            $.post('{{ route('branches.multiDelete') }}', {
                ids: ids,
                _token: csrf
            }, function(res) {
                if (res.success) ids.forEach(id => $('input[value="' + id + '"]').closest('tr').remove());
            });
        });

        // بحث مباشر
        $('#search').keyup(function() {
            let q = $(this).val();
            $.get('{{ route('branches.search') }}', {
                q: q
            }, function(html) {
                $('#branches-table').html(html);
            });
        });

        // تحديد الكل
        $('#select-all').change(function() {
            $('input[name="branches[]"]').prop('checked', $(this).prop('checked'));
        });
    </script>
@endpush
