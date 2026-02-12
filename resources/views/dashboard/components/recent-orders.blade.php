<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">آخر الطلبات</h3>
        <a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm">
            عرض الكل
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
        </a>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#ORD-001</td>
                    <td>أحمد محمد</td>
                    <td>2024-01-15</td>
                    <td>$450</td>
                    <td>
                        <span class="status status-completed">مكتمل</span>
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background: #4e73df; color: #fff;" onclick="viewOrder(1)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-002</td>
                    <td>سارة أحمد</td>
                    <td>2024-01-15</td>
                    <td>$280</td>
                    <td>
                        <span class="status status-processing">قيد المعالجة</span>
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background: #4e73df; color: #fff;" onclick="viewOrder(2)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-003</td>
                    <td>محمد علي</td>
                    <td>2024-01-14</td>
                    <td>$890</td>
                    <td>
                        <span class="status status-pending">قيد الانتظار</span>
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background: #4e73df; color: #fff;" onclick="viewOrder(3)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-004</td>
                    <td>فاطمة الزهراء</td>
                    <td>2024-01-14</td>
                    <td>$350</td>
                    <td>
                        <span class="status status-completed">مكتمل</span>
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background: #4e73df; color: #fff;" onclick="viewOrder(4)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-005</td>
                    <td>عمر حسن</td>
                    <td>2024-01-13</td>
                    <td>$620</td>
                    <td>
                        <span class="status status-processing">قيد المعالجة</span>
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background: #4e73df; color: #fff;" onclick="viewOrder(5)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewOrder(orderId) {
    window.location.href = `/orders/${orderId}`;
}
</script>