getData()
function getData(){
  $.ajax({
        url:"groups/getData",
        type:"POST",
        success:function(result){
            result = JSON.parse(result)
            // console.log(result)
            if (result.count > 0) {
              let rows = "";

              for (let i = 0; i < result.count; i++) {
                  rows += "<tr>";
                  rows += "<td>" + (result[i]?.dn ?? "") + "</td>";
                  rows += "<td>" + (result[i]?.ou?.[0] ?? "") + "</td>";
                  rows += `<td><button data-nama='${result[i]?.ou?.[0] ?? ""}' onclick='edit(this)' class='btn btn-1'>Edit</button>
                              <button class='btn btn-1' onclick='btnHapus("${result[i]?.ou?.[0] ?? ""}")'>Hapus</button>
                            </td>`;
                  rows += "</tr>";
              }

              $('#tableData').children('tbody:first').html(rows);
          } else {
              $('#tableData').children('tbody:first').html("<tr><td colspan='5'>Tidak ada data ditemukan.</td></tr>");
          }
        }
    })
}

function btnHapus(nama){
  Swal.fire({
    html: `Hapus group?<br><b>${nama}</b>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya",
    focusConfirm: false,
    allowOutsideClick: false,
    showClass: {
      popup: '' 
    }
  }).then((result) => {
    if (result.isConfirmed) {
        hapus(nama)
    }
  })
}

function hapus(nama){
  $.ajax({
        url:"groups/hapus",
        type:"POST",
        data: ({nama: nama}),
        success:function(result){
          // console.log(result)
          if(result == 'sukses'){
            toastMixin.fire({
                icon: 'success',
                title: 'Data berhasil dihapus.'
            });
          }else{
            toastMixin.fire({
                icon: 'error',
                title: 'Data gagal dihapus.'
            });
          }

          getData()
        }
    })
}

function save(mode){
  const ouLama = $('.lama').val()
  const ouBaru = $('.baru').val()

  const url = mode === 1 ? "groups/ubah" : "groups/tambah";

  $.ajax({
        url:url,
        type:"POST",
        data: ({
          ouLama: ouLama,
          ouBaru: ouBaru,
        }),
        success:function(result){
          console.log(result)
          if(result == 'sukses'){
            toastMixin.fire({
                icon: 'success',
                title: 'Data berhasih disimpan.'
            });
          }else{
            toastMixin.fire({
                icon: 'error',
                title: 'Data gagal disimpan.'
            });
          }
          $("#modaladd").modal('hide');
          getData()
        }
    })
}

function edit(el) {
    $('.btnSave').removeAttr('onclick');
    $('.btnSave').attr('onclick', 'save(1)');

    let $btn = $(el);

    let nama     = $btn.data("nama");

    // Isi ke form edit
    $(".lama, .baru").val(nama);

    $(".modal-title").text('Ubah Group');
    $("#modaladd").modal('show');
}

function tambah(){
  $('.btnSave').removeAttr('onclick');
  $('.btnSave').attr('onclick', 'save(2)');

  $('.lama, .baru').val('')
  $(".modal-title").text('Tambah Group');
}

