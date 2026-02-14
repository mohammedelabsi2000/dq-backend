<!-- AddModal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <form id="add-form">@csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مساجد جماعية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rows">
                        <div class="row mb-2">
                            <div class="col">
                                <input type="text" name="name[]" class="form-control" placeholder="اسم المسجد"
                                    required>
                            </div>
                            <div class="col">
                                <input type="text" name="notes[]" class="form-control" placeholder="ملاحظات">
                            </div>
                            <div class="col">
                                <select name="branch[]" class="form-control branch-select" required>
                                    <option value="">اختر الفرع</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <select name="region_id[]" class="form-control region-select" required>
                                    <option value="">اختر المنطقة</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" onclick="addRow()">+ إضافة سطر</button>
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
    function addRow() {
        let row = `<div class="row mb-2">
        <div class="col"><input type="text" name="name[]" class="form-control" placeholder="اسم المسجد" required></div>
        <div class="col"><input type="text" name="notes[]" class="form-control" placeholder="ملاحظات"></div>
        <div class="col">
            <select name="branch[]" class="form-control branch-select" required>
                <option value="">اختر الفرع</option>
                @foreach ($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select name="region_id[]" class="form-control region-select" required>
                <option value="">اختر المنطقة</option>
            </select>
        </div>
    </div>`;
        $('#rows').append(row);
    }

    // تحميل المناطق عند تغيير الفرع
    $(document).on('change', '.branch-select', function() {
        let branch_id = $(this).val();
        let regionSelect = $(this).closest('.row').find('.region-select');
        if (branch_id) {
            $.get('/regions', {
                branch_id: branch_id
            }, function(data) {
                let options = '<option value="">اختر المنطقة</option>';
                data.forEach(r => options += `<option value="${r.id}">${r.name}</option>`);
                regionSelect.html(options);
            });
        }
    });
</script>
