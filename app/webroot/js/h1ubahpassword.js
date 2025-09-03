let toastMixin = Swal.mixin({
    toast: true,
    position: 'top-right',
    showConfirmButton: false,
    timer: 2000,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});


function save(){
  const username = $('.user').val()
  const passwordlama = $('.passold').val()
  const passwordbaru = $('.passnew').val()

  if(username === '' || passwordlama === '' || passwordbaru === ''){
        toastMixin.fire({
          icon: 'warning',
          title: 'Username atau password kosong.'
        })
        return
    }
    
    $.ajax({
        url:"ubahpasswords/save",
        data:({
            username:username,
            passwordlama:passwordlama,
            passwordbaru:passwordbaru
            }),
        type:"POST",
        beforeSend: function() {
        },
        success:function(result){
          // console.log(result);return
          if(result.includes('karakter') || result.includes('besar') || result.includes('kecil') || result.includes('angka') || result.includes('salah')){
            Swal.fire({
              icon: 'warning',
              title: result,
              confirmButtonText: "OK"
            })
          }else if(result==='sukses'){
            Swal.fire({
              icon: 'success',
              title: 'Berhasil ganti password.',
              confirmButtonText: "OK"
            })
          }else{
            Swal.fire({
              icon: 'warning',
              title: 'Jangan input password sebelumnya.',
              confirmButtonText: "OK"
            })
          }
        },error: function(xhr, status, error) {
          toastMixin.fire({
            icon: 'error',
            title: 'Terjadi kesalahan.'
          })
        },
        complete: function() {
          // Swal.close();
        }
    })
}

