<<<<<<< HEAD
@extends('layouts.app')
@section('title', 'إدارة المساجد')
@section('content')
    <div class="container">
        <h3>إدارة المساجد</h3>
        <div class="d-flex mb-3">
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addModal">+ إضافة مسجد</button>
            <button class="btn btn-danger" id="delete-selected">حذف المحدد</button>
            <input type="text" id="search" class="form-control w-25 ms-auto" placeholder="بحث...">
        </div>

        <div id="mosque-table">
            @include('mosques.partials.table', ['mosques' => $mosques])
        </div>

        @include('mosques.partials.addModal')
        @include('mosques.partials.editModal')
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#mosque-table table').DataTable({
                "paging": true,
                "searching": false,
                "ordering": true
            });
        });

        // البحث مباشر
        $('#search').on('keyup', function() {
            $.get("{{ route('mosques.search') }}", {
                q: $(this).val()
            }, function(data) {
                $('#mosque-table').html(data);
                $('#mosque-table table').DataTable({
                    "paging": true,
                    "searching": false,
                    "ordering": true
                });
            });
        });

        // حذف فردي وجماعي
        $(document).on('click', '.delete-btn', function() {
            if (confirm('هل أنت متأكد؟')) {
                let id = $(this).data('id');
                $.ajax({
                    url: '/mosques/destroy/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        $('#search').trigger('keyup');
                    }
                });
            }
        });
        $('#delete-selected').click(function() {
            let ids = [];
            $('input.select-mosque:checked').each(function() {
                ids.push($(this).val());
            });
            if (ids.length == 0) {
                alert('اختر مسجد واحد على الأقل');
                return;
            }
            if (confirm('هل أنت متأكد؟')) {
                $.post("{{ route('mosques.deleteMultiple') }}", {
                    _token: '{{ csrf_token() }}',
                    ids
                }, function() {
                    $('#search').trigger('keyup');
                });
            }
        });
    </script>
@endsection
=======
<!-- New AJAX-based interface for mosque management -->
<!-- This section will handle displaying and managing mosques asynchronously -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Function to fetch mosques and display them
        function fetchMosques() {
            $.ajax({
                url: '/api/mosques',
                method: 'GET',
                success: function(data) {
                    $('#mosque-list').empty();
                    data.forEach(function(mosque) {
                        $('#mosque-list').append(`<li>${mosque.name}</li>`);
                    });
                }
            });
        }

        // Call function on page load
        fetchMosques();

        // Function to add a new mosque
        $('#add-mosque-form').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '/api/mosques',
                method: 'POST',
                data: { name: $('#mosque-name').val() },
                success: function() {
                    fetchMosques();
                    $('#mosque-name').val(''); // Clear input
                }
            });
        });
    });
</script>

<h2>Mosque Management</h2>
<form id="add-mosque-form">
    <input type="text" id="mosque-name" placeholder="Enter mosque name" required />
    <button type="submit">Add Mosque</button>
</form>

<ul id="mosque-list">
    <!-- Mosque items will be populated here by AJAX -->
</ul>
>>>>>>> 91150221189a30c086e16e29e151cc0da866bd24
