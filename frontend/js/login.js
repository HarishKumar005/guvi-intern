// frontend/js/login.js
$(function() {
  $('#loginBtn').on('click', function() {
    const email = $('#email').val().trim();
    const password = $('#password').val();

    $('#msg').text('');

    $.ajax({
      url: '/backend/login.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ email, password }),
      success: function(res) {
        if (res.success && res.token) {
          // store token and redirect
          localStorage.setItem('auth_token', res.token);
          window.location.href = 'profile.html';
        } else {
          $('#msg').html('<div class="alert alert-danger">' + (res.error || 'Login failed') + '</div>');
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
