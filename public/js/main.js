$(document).ready(function () {
    $("#url_form").submit(function (event) {
        event.preventDefault();
        var long_url = $("input[name='long_url']").val();
        $.ajax({
            url: '/shortener/shorten',
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: {long_url: long_url},
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                if (data.err_code == '0000') {
                    $('#short_url').text(data.short_url);
                    $('#short_url').show();
                } else {
                    $('#short_url').text(data.err_msg);
                    $('#short_url').show();
                }
            }
        });
    });
});