<form action="" id="unsplash_search_form" class="ui form">
    <div class="ui icon input">
        <input type="text" name="q" placeholder="Search...">
        <i class="search link icon" id="unsplash_run_search"></i>
    </div>
    <div class="ui divider"></div>
    <style>
        .unsplash-results-area .selectableimage {
            width: 200px;
            height: 133px;
            overflow: hidden;
        }
    </style>
    <div class="unsplash-results-area"></div>

    <script>
        $("#unsplash_run_search").click(function(e) {
            e.preventDefault();

            var searchValue = $("#unsplash_search_form input[name=q]").val();

            $.getJSON('/cms/unsplash/runsearch/{$blog->id}?q=' + searchValue, function(data) {
                var html = "";

                for (var image = 0; image < data.results.length; image++) {
                    html += '<img src="' + data.results[image].urls.thumb + '" class="selectableimage" data-large="' + data.results[image].urls.regular + '">';
                }

                html += '<input type="hidden" id="choosen_image_large">';
                html += '<input type="hidden" id="choosen_image_thumb">';
                html += '<button class="ui teal button" id="unsplash_upload_button" disabled="disabled">Upload and insert</button>';

                $('#unsplash_search_form .unsplash-results-area').html(html);

                $(".selectableimage").click(function() {
                    $(".selectableimage").css('border','0');
                    $(this).css('border','4px solid green');
                    $("#choosen_image_large").val($(this).data('large'));
                    $("#choosen_image_thumb").val($(this).attr('src'));

                    $("#unsplash_upload_button").removeAttr("disabled");
                });

                // Insert an existing image into the post
                $("#unsplash_upload_button").click(function(e) {
                    e.preventDefault();
                    if ($("#choosen_image_large").val() === "") {
                        alert("Please select an image");
                        return false;
                    }

                    $.post('/cms/unsplash/upload/{$blog->id}', { url: $("#choosen_image_large").val(), csrf_token: CSRFTOKEN }, function(data) {
                        if (data.error) {
                            console.error('Failed :(');
                        }
                        else {
                            return closeUploadWindow(data.thumb);
                        }
                    });
                });
            });
        });
    </script>
</form>