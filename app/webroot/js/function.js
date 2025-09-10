// $(document).ready(function(){
    // var pathname = window.location.pathname; 
    // var segments = pathname.split('/'); 
    // var segment = segments[2];

    // console.log(segment)
    // if(segment=='mainmenus' || segment==''){
    //     $('#homeMenu').addClass('active')
    //     $('#headerMenu').text('BNPwifi')
    // }else if(segment=='masterclients'){
    //     $('#clientMenu').addClass('active')
    //     $('#headerMenu').text('Client')
    // }else if(segment=='laporans'){
    //     $('#laporanMenu').addClass('active')
    //     $('#headerMenu').text('Laporan')
    // }
    // getNotif()
// });

$(document).ajaxStart(function(){
    $('#loading').fadeIn();
}).ajaxStop(function(){
    $('#loading').fadeOut();
});

let toastMixin = Swal.mixin({
    toast: true,
    position: 'top-right',
    showConfirmButton: false,
    timer: 3000,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// function page(halaman){
//     if(navigator.onLine) {
//         $('#loading').fadeIn();
//         cekServer(halaman);
//     } else {
//     toastMixin.fire({
//         animation: true,
//         icon: 'warning',
//         title: 'Tidak ada koneksi internet.'
//         });
//     return
//     }
// }

// function cekServer(halaman) {
//     $.ajax({
//         url: halaman,
//         method: "GET",
//         timeout: 5000,
//         success: function(response) {
//             window.location.replace(halaman);
//         },
//         error: function() {
//             toastMixin.fire({
//                 animation: true,
//                 icon: 'error',
//                 title: 'Server tidak merespon.'
//             });
//         }
//     });
// }

// function getNotif(){
//     $.ajax({
//         url: 'mainmenus/getNotif',
//         type: "POST",
//         dataType: "text",
//         success: function(result){ 
//             // console.log(result);return
//             result = result.split("^")
//             $("#jmlNotif").html(result[0])
//             $("#isiNotif").html(result[1])
//         }
//    	});	
// }

// function logout(){
//     Swal.fire({
//         title: 'Anda ingin logout?',
//         imageUrl: "img/logout2.png",
//         showCancelButton: true,
//         confirmButtonText: "Ya",
//         // focusCancel: true,
//         allowOutsideClick: false
//       }).then((result) => {
//         if (result.isConfirmed) {
//             window.location.replace('logout');
//         }
//       });
// }
