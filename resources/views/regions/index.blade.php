@extends('layouts.app')
<<<<<<< HEAD
@section('title', 'المناطق')
@section('content')
    <div class="container">

        <h2 class="page-title mb-4">المناطق</h2>

        {{-- <input type="text" id="search" class="form-control mb-3" placeholder="ابحث عن المنطقة أو الفرع..."> --}}
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" id="search-name" class="form-control" placeholder="ابحث عن المنطقة...">
            </div>
            <div class="col-md-6">
                <select id="search-branch" class="form-control">
                    <option value="">كل الفروع</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- جدول المناطق -->
        <form id="regions-form">
            <table class="table table-bordered bg-white shadow-sm rounded">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                        <th>الاسم</th>
                        <th>الفرع</th>
                        <th>ملاحظات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="regions-table">
                    @include('regions.index_table', ['regions' => $regions])
                </tbody>
            </table>
            <button type="button" id="delete-selected" class="btn btn-danger mt-2"><i class="fas fa-trash"></i> حذف
                المختار</button>
        </form>

        <!-- إضافة مناطق متعددة بشكل سلس -->
        <h4 class="mt-5 mb-3">إضافة مناطق متعددة</h4>
        <form id="multi-regions-form" class="bg-white p-4 rounded shadow-sm">
            @csrf
            <div id="regions-wrapper">
                <div class="region-item border rounded p-3 mb-3 shadow-sm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name[]" placeholder="اسم المنطقة" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الفرع</label>
                            <select name="branch_id[]" class="form-control" required>
                                <option value="">اختر فرع</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ملاحظات</label>
                            <input type="text" name="notes[]" placeholder="ملاحظات" class="form-control">
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-region"><i class="fas fa-trash"></i>
                            حذف</button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-info" id="add-region"><i class="fas fa-plus"></i> إضافة منطقة
                    أخرى</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> حفظ المناطق</button>
            </div>
        </form>

        <!-- تعديل Modal -->
        <div class="modal fade" id="editRegionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="edit-region-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">تعديل المنطقة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-region-id">
                            <div class="mb-3">
                                <label>الاسم</label>
                                <input type="text" id="edit-name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>الفرع</label>
                                <select id="edit-branch" class="form-control" required>
                                    <option value="">اختر فرع</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>ملاحظات</label>
                                <input type="text" id="edit-notes" class="form-control">
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

        // إضافة منطقة جديدة
        $('#add-region').click(function() {
            let newRegion = $('.region-item:first').clone();
            newRegion.find('input,select').val('');
            $('#regions-wrapper').append(newRegion);
        });

        // إزالة منطقة
        $(document).on('click', '.remove-region', function() {
            if ($('.region-item').length > 1) $(this).closest('.region-item').remove();
        });

        // حفظ مناطق متعددة
        $('#multi-regions-form').submit(function(e) {
            e.preventDefault();
            let names = $("input[name='name[]']").map(function() {
                return $(this).val()
            }).get();
            let branch_ids = $("select[name='branch_id[]']").map(function() {
                return $(this).val()
            }).get();
            let notes = $("input[name='notes[]']").map(function() {
                return $(this).val()
            }).get();

            for (let i = 0; i < names.length; i++) {
                if (branch_ids[i] == '') {
                    alert('اختر فرع لكل منطقة');
                    return;
                }
            }

            $.post('{{ route('regions.store') }}', {
                _token: csrf,
                name: names,
                branch_id: branch_ids,
                notes: notes
            }, function(res) {
                if (res.success) {
                    $('#regions-table').prepend(res.regions);
                    $('#regions-wrapper .region-item:not(:first)').remove();
                    $('#multi-regions-form')[0].reset();
                }
            });
        });

        // تعديل منطقة
        $(document).on('click', '.edit-btn', function() {
            let row = $(this).closest('tr');
            let id = $(this).data('id');
            $('#edit-region-id').val(id);
            $('#edit-name').val(row.find('td:eq(1)').text());
            $('#edit-branch').val(row.find('td:eq(2)').data('id'));
            $('#edit-notes').val(row.find('td:eq(3)').text());
            $('#editRegionModal').modal('show');
        });

        // حفظ تعديل
        $('#edit-region-form').submit(function(e) {
            e.preventDefault();
            let id = $('#edit-region-id').val();
            $.ajax({
                url: '/regions/' + id,
                type: 'PUT',
                data: {
                    _token: csrf,
                    name: $('#edit-name').val(),
                    branch_id: $('#edit-branch').val(),
                    notes: $('#edit-notes').val()
                },
                success: function(res) {
                    if (res.success) {
                        $('input[value="' + id + '"]').closest('tr').replaceWith(res.region);
                        $('#editRegionModal').modal('hide');
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
            let ids = $('input[name="regions[]"]:checked').map(function() {
                return this.value
            }).get();
            if (ids.length === 0) {
                alert('اختر مناطق');
                return;
            }
            if (!confirm('هل أنت متأكد؟')) return;
            $.post('{{ route('regions.multiDelete') }}', {
                ids: ids,
                _token: csrf
            }, function(res) {
                if (res.success) ids.forEach(id => $('input[value="' + id + '"]').closest('tr').remove());
            });
        });

        // // بحث مباشر
        // $('#search').keyup(function() {
        //     let q = $(this).val();
        //     $.get('{{ route('regions.search') }}', {
        //         q: q
        //     }, function(html) {
        //         $('#regions-table').html(html);
        //     });
        // });

        // // تحديد الكل
        // $('#select-all').change(function() {
        //     $('input[name="regions[]"]').prop('checked', $(this).prop('checked'));
        // });

        function searchRegions() {
            let q = $('#search-name').val();
            let branch_id = $('#search-branch').val();

            $.get('{{ route('regions.search') }}', {
                q: q,
                branch_id: branch_id
            }, function(html) {
                $('#regions-table').html(html);
            });
        }

        // البحث النصي
        $('#search-name').keyup(function() {
            searchRegions();
        });

        // اختيار الفرع
        $('#search-branch').change(function() {
            searchRegions();
        });
    </script>
@endpush
=======

@section('content')
<div class="container">
    <h2 class="mb-3">قائمة المناطق</h2>

    <a href="{{ route('regions.create') }}" class="btn btn-primary mb-3">إضافة منطقة جديدة</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الفرع</th>
                <th>ملاحظات</th>
                <th>التحكم</th>
            </tr>
        </thead>
        <tbody>
        @forelse($regions as $region)
            <tr>
                <td>{{ $region->id }}</td>
                <td>{{ $region->name }}</td>
                <td>{{ $region->branch->name ?? '-' }}</td>
                <td>{{ $region->notes ?? '-' }}</td>
                <td>
                    <a href="{{ route('regions.show', $region) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('regions.edit', $region) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('regions.destroy', $region) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">لا توجد مناطق</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $regions->links() }}
</div>
@endsection
>>>>>>> 91150221189a30c086e16e29e151cc0da866bd24
