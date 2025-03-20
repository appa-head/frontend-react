(function ($) {
    'use strict';

    $(document).ready(function () {

        $(document).on("click", "#wpd-activate", function () {
            var code = 0;
            request_registration(code);
        });

        function request_registration(code) {
            $("#spinner").css({display: "inline-block"});
            $.post(
                    ajaxurl,
                    {
                        action: "wpd_activate_license",
                        code: code
                    },
            function (data) {
                $("#spinner").css({display: "none"});
                switch (data) {
                    case "purchase_code_used_on_different_site":
                        if (confirm("This purchase code has been used for another website. Do you want to use it anyway? (this will deactivate the previous website activated)")) {
                            var code = 1;
                            request_registration(code);
                        } else {
                            $('#license-message').html("<p>Operation cancelled.</p>");
                        }
                        break;
                    case "200":
                        alert("Activation succeded! Your product is now activated");
                        document.location.reload(true);
                        break;
                    default:
                        alert(data);
                        $('#license-message').html("<p>" + data + "</p>");
                        break;
                }
            })
                    .fail(function (xhr, status, error) {
                        $("#spinner").css({display: "none"});
                        $('#license-message').html("<p>Activation failed due to a network error. Please, retry later. </p>");
                        alert("Activation failed due to a network error. Please, retry later.");
                    });
        }
    });


})(jQuery);
