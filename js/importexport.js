(function ($) {
    $.discountcards_importexport = {
        options: {},
        init: function (options) {
            this.options = options;
            this.uploadInit();
            this.submitInit();
        },
        uploadInit: function () {
            var url = $('.fileupload:first').data('action');
            var upload = $('.fileupload:first').parents('div.field');
            $('.fileupload:first').fileupload({
                url: url,
                dataType: 'json',
                start: function () {
                    upload.find('.fileupload:first').hide();
                    upload.find('.js-fileupload-progress').show();
                },
                done: function (e, data) {
                    var response = data.jqXHR.responseJSON;
                    if (response.status == 'ok') {
                        upload.find('.js-fileupload-progress').html('<i class="icon16 yes"></i>');
                    } else {
                        upload.find('.js-fileupload-progress').html('<i class="icon16 no"></i> ' + response.errors.join(','));
                    }
                    upload.find('.fileupload:first').show();
                    setTimeout(function () {
                        upload.find('.js-fileupload-progress').empty();
                    }, 3000);

                },
                fail: function (e, data) {
                    upload.find('.js-fileupload-progress').html('<i class="icon16 no"></i>');
                }
            });
        },
        submitInit: function () {
            $('form').submit(function () {
                var form = $(this);
                $.ajax({
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    dataType: 'json',
                    type: 'post',
                    success: function (response) {
                        console.log(response);
                        if (response.status == 'ok') {
                            form.find('.response').html('<i class="icon16 yes"></i> ' + response.data.html);
                        } else {
                            form.find('.response').html('<i class="icon16 no"></i> ' + response.errors.join(','));
                        }
                    },
                    error: function (response) {
                        form.find('.response').html('<i class="icon16 no"></i> ' + response.responseText);
                    }
                });
                return false;
            });

        }
    };
})(jQuery);