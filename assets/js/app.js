/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import '../css/app.scss';

const $ = require('jquery');
require('bootstrap');
import toastr from 'toastr/build/toastr.min';
require('summernote');


$(document).ready(function() {
    $('.string-translation').change(function() {
        var cell = $(this).parents('td'),
            id = $(this).data('uuid'),
            language = $(this).data('language');
        $.post('/translation/string/' + id + '/' + language, {text: $(this).val()}, function(resp) {
            if(resp.success) {
                toastr["success"]("Saved");
                cell.removeClass('untranslated');
            } else {
                toastr["error"]("Something went wrong. Please try again later");
            }
        });
    });

    var SaveButton = function (context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: 'Save',
            tooltip: 'Save changes',
            click: function () {
                var element = $(this).parents('.col-4').find('.page'),
                    id = element.data('uuid'),
                    language = element.data('language');
                $.post('/translation/page/' + id + '/' + language, {text: element.summernote('code')}, function() {
                    toastr["success"]("Saved")
                });
                return false;
            }
        });
        return button.render();   // return button as jquery object
    };

    $('.page').summernote({
        toolbar: [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['insert', ['link','hr']],
            ['misc',['codeview']],
            ['mybutton',['save']]
        ],
        buttons: {save: SaveButton}
    });
});