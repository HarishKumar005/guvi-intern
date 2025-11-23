// frontend/js/profile.js
$(function() {
  const token = localStorage.getItem('auth_token');
  const $msg = $('#msg');

  if (!token) {
    window.location.href = 'login.html';
    return;
  }

  function showMessage(html, isError = false) {
    $msg.html('<div class="alert ' + (isError ? 'alert-danger' : 'alert-success') + '">' + html + '</div>');
    setTimeout(() => $msg.html(''), 4000);
  }

  // fetch profile
  $.ajax({
    url: '/backend/profile_get.php',
    method: 'POST',
    contentType: 'application/json',
    headers: { 'Authorization': 'Bearer ' + token },
    data: JSON.stringify({}),
    success: function(res) {
      if (res.success && res.user) {
        const u = res.user;
        $('#name').val(u.name || '');
        $('#email').val(u.email || '');
        $('#age').val(u.age || '');
        $('#dob').val(u.dob || '');
        $('#contact').val(u.contact || '');
      } else {
        localStorage.removeItem('auth_token');
        window.location.href = 'login.html';
      }
    },
    error: function() {
      localStorage.removeItem('auth_token');
      window.location.href = 'login.html';
    }
  });

  $('#saveBtn').on('click', function() {
    const payload = {
      name: $('#name').val().trim(),
      age: $('#age').val() ? parseInt($('#age').val(), 10) : null,
      dob: $('#dob').val() || null,
      contact: $('#contact').val().trim() || null
    };
    $.ajax({
      url: '/backend/profile_update.php',
      method: 'POST',
      contentType: 'application/json',
      headers: { 'Authorization': 'Bearer ' + token },
      data: JSON.stringify(payload),
      success: function(res) {
        if (res.success) {
          showMessage('Profile updated successfully.');
        } else {
          showMessage(res.error || 'Update failed', true);
        }
      },
      error: function(xhr) {
        let t = 'Server error';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) t = xhr.responseJSON.error;
        showMessage(t, true);
      }
    });
  });
  // UPLOAD PHOTO
$('#uploadPhotoBtn').on('click', function(){
    const file = $('#photoUpload')[0].files[0];
    if(!file){
        showMessage('Select a file first', true);
        return;
    }

    let fd = new FormData();
    fd.append('photo', file);
    fd.append('token', token);

    $.ajax({
        url: '/backend/upload_photo.php',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res){
            if(res.success){
                $('#profilePreview').attr('src', '/backend/uploads/profile/' + res.photo).show();
                $('#deletePhotoBtn').show();
            } else {
                showMessage(res.error, true);
            }
        }
    });
});

// DELETE PHOTO
$('#deletePhotoBtn').on('click', function(){
    $.ajax({
        url: '/backend/delete_photo.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ token }),
        success: function(res){
            if(res.success){
                $('#profilePreview').hide().attr('src', '');
                $('#deletePhotoBtn').hide();
                showMessage('Photo deleted successfully!');
            }
        }
    });
});

  $('#logoutBtn').on('click', function() {
    // call backend logout to remove session from Redis
    $.ajax({
      url: '/backend/logout.php',
      method: 'POST',
      contentType: 'application/json',
      headers: { 'Authorization': 'Bearer ' + token },
      data: JSON.stringify({}),
      complete: function() {
        localStorage.removeItem('auth_token');
        window.location.href = 'login.html';
      }
    });
  });
});
// CHANGE PASSWORD
$('#changePassBtn').on('click', function () {
    const currentPassword = $('#currentPassword').val();
    const newPassword = $('#newPassword').val();
    const confirmNewPassword = $('#confirmNewPassword').val();

    $.ajax({
        url: '/backend/change_password.php',
        method: 'POST',
        contentType: 'application/json',
        headers: { 'Authorization': 'Bearer ' + token },
        data: JSON.stringify({
            currentPassword,
            newPassword,
            confirmNewPassword
        }),
        success: function (res) {
            if (res.success) {
                showMessage('Password updated successfully!');
                $('#currentPassword').val('');
                $('#newPassword').val('');
                $('#confirmNewPassword').val('');
            } else {
                showMessage(res.error || 'Password update failed!', true);
            }
        },
        error: function (xhr) {
            let t = 'Server error';
            if (xhr.responseJSON && xhr.responseJSON.error) t = xhr.responseJSON.error;
            showMessage(t, true);
        }
    });
});
