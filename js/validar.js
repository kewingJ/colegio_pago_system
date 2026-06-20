$(document).ready(function() {
    //validar login
    $('#Form1').bootstrapValidator({
        message: '',
        feedbackIcons: {
    },
    fields: {
        email: {
            validators: {
                notEmpty: {
                    message: ''
                }
            }
        },
        password: {
            validators: {
                notEmpty: {
                    message: ''
                }
            }
        }
    }
    });
});