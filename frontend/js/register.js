// frontend/js/register.js
$(function() {
  $('#registerBtn').on('click', function() {
    const name = $('#name').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();

    $('#msg').text('');

    $.ajax({
      url: '/backend/register.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ name, email, password }),
      success: function(res) {
        if (res.success) {
          $('#msg').html('<div class="alert alert-success">Registered successfully. <a href="login.html">Login now</a></div>');
          $('#registerForm')[0].reset();
        } else {
          $('#msg').html('<div class="alert alert-danger">' + (res.error || 'Unknown error') + '</div>');
        }
      },
      error: function(xhr) {
        let text = 'Server error';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) text = xhr.responseJSON.error;
        $('#msg').html('<div class="alert alert-danger">' + text + '</div>');
      }
    });
  });
});
