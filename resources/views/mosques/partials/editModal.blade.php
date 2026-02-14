<!-- EditModal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form id="edit-form">@csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل المسجد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-2">
                        <label>اسم المسجد</label>
                        <input type="text" id="edit-name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>ملاحظات</label>
                        <input type="text" id="edit-notes" name="notes" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>الفرع</label>
                        <select id="edit-branch" name="branch" class="form-control" required>
                            <option value="">اختر الفرع</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>المنطقة</label>
                        <select id="edit-region" name="region_id" class="form-control" required>
                            <option value="">اختر المنطقة</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">حفظ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // تحميل المناطق عند تغيير الفرع في التعديل
    $('#edit-branch').change(function() {
        let branch_id = $(this).val();
        if (branch_id) {
            $.get('/regions', {
                branch_id: branch_id
            }, function(data) {
                let options = '<option value="">اختر المنطقة</option>';
                data.forEach(r => options += `<option value="${r.id}">${r.name}</option>`);
                $('#edit-region').html(options);
            });
        }
    });
</script>
