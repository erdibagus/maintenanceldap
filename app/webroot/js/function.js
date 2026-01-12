let tableData, listMail = []

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