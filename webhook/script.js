$(document).ready(function() {

    function kirimRequest(aksi) {
        const token = $('#api-token').val();
        const responseArea = $('#api-response');

        if (token === '') {
            responseArea.val("ERROR: API Token tidak boleh kosong.");
            return;
        }

        $.ajax({
            url: 'connection',      // File tujuan
            type: 'POST',               // Metode pengiriman data
            data: {                     // Data yang dikirim
                action: aksi,
                api_token: token
            },
            dataType: 'json',           // Tipe data yang diharapkan dari server
            success: function(response) {
                const responseText = JSON.stringify(response, null, 2);
                alert(responseText);
                responseArea.val(responseText);
            },
            error: function() {
                responseArea.val('ERROR: Tidak dapat terhubung ke server. Pastikan file connection ada.');
            }
        });
    }

    $('#set-btn').click(function() {
        kirimRequest('set');
    });

    $('#check-btn').click(function() {
        kirimRequest('check');
    });

    $('#delete-btn').click(function() {
        kirimRequest('delete');
    });

});
