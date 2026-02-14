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
