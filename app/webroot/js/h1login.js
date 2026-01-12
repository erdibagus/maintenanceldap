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


function login(){
  const username = $('.user').val()
  const password = $('.pass').val()

  if(username === '' || password === ''){
        toastMixin.fire({
          icon: 'warning',
          title: 'Username atau password kosong.'
        })
        return
    }

  if(username === 'admin' && password === 'admin'){
    window.location.replace('homes');
  }else{
    toastMixin.fire({
          icon: 'warning',
          title: 'Username atau password salah.'
        })
        return
  }
    
  return
    // $.ajax({
    //     url:"logProcess",
    //     data:({
    //         username:username,
    //         password:password
    //         }),
    //     type:"POST",
    //     beforeSend: function() {
    //     },
    //     success:function(result){
    //       console.log(result)
    //       if(result.includes('sukses')){
           
    //         result = result.split('!');
    //         const waktu = result[1] * 1000;
    //         let timerInterval;

    //         Swal.fire({
    //           title: "Login berhasil.",
    //           icon: "success",
    //           timer: waktu, 
    //           footer: 'Expired dalam: &nbsp <b></b>',
    //           didOpen: () => {
    //             const timer = Swal.getPopup().querySelector("b");
    //             timerInterval = setInterval(() => {
    //               let ms = Swal.getTimerLeft(); // milidetik
    //               if (ms !== null) {
    //                 let totalSeconds = Math.floor(ms / 1000);
    //                 let hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    //                 let minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    //                 let seconds = String(totalSeconds % 60).padStart(2, '0');
    //                 timer.textContent = `${hours}:${minutes}:${seconds}`;
    //               }
    //             }, 1000); // update tiap detik
    //           },
    //           willClose: () => {
    //             clearInterval(timerInterval);
    //           }
    //         });

    //       }else if(result==='password'){
    //         Swal.fire({
    //           icon: 'warning',
    //           title: 'Password salah.',
    //           confirmButtonText: "OK"
    //         })
    //       }else if(result==='expired'){
    //         Swal.fire({
    //           title: 'Akun terkunci!',
    //           imageUrl: "img/lock.png",
    //           html: "<b>Password expired.</b>",
    //           confirmButtonText: "OK"
    //         })
    //       }else if(result.includes('salahpw3x')){
    //         result = result.split('!')
    //         const waktu = result[1] * 1000;
    //         let timerInterval;
    //         Swal.fire({
    //           title: "Akun terkunci!",
    //           imageUrl: "img/lock.png",
    //           html: "<strong>Salah password 3x</strong>",
    //           timer: waktu, 
    //           timerProgressBar: true,
    //           footer: 'Sisa waktu: &nbsp <b></b>',
    //           didOpen: () => {
    //             Swal.showLoading();
    //             const timer = Swal.getPopup().querySelector("b");
    //             timerInterval = setInterval(() => {
    //               let ms = Swal.getTimerLeft(); // milidetik
    //               if (ms !== null) {
    //                 let totalSeconds = Math.floor(ms / 1000);
    //                 let hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    //                 let minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    //                 let seconds = String(totalSeconds % 60).padStart(2, '0');
    //                 timer.textContent = ` ${hours}:${minutes}:${seconds}`;
    //               }
    //             }, 100);
    //           },
    //           willClose: () => {
    //             clearInterval(timerInterval);
    //           }
    //         });
    //       }else{
    //         Swal.fire({
    //           icon: 'warning',
    //           title: 'Username salah.',
    //           confirmButtonText: "OK"
    //         })
    //       }
    //     },error: function(xhr, status, error) {
    //       toastMixin.fire({
    //         icon: 'error',
    //         title: 'Terjadi kesalahan.'
    //       })
    //     },
    //     complete: function() {
    //       // Swal.close();
    //     }
    // })
}

