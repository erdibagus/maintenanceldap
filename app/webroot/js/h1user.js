let tableData
$(document).ready(function(){
getGroup()
})
function getGroup(){
  $.ajax({
        url:"groups/getData",
        success:function(result){
            result = JSON.parse(result)
            // console.log(result)
            if (result.count > 0) {
              let rows = "<option value=''>--Pilih--</option>";

              for (let i = 0; i < result.count; i++) {
                  if((result[i]?.ou?.[0] ?? "") === 'policies') continue
                  rows += "<option value='" + (result[i]?.ou?.[0] ?? "") + "'>" + (result[i]?.ou?.[0] ?? "") + "</option>";
              }

              $('.ou, .formOu').html(rows);
          } else {
              $('.ou').html("<option>--Tidak ada data--</option>");
          }
        }
    })
}
function getData(mode,ouSave){
  let ou = $('.ou').val()
  const nama = $('.filterNama').val()

  if(mode == 1){
    ou = ouSave
  } else {
    if (!ou) return toastMixin.fire({
                      icon: 'warning',
                      title: 'Pilih OU.'
                  });
  }

  $.ajax({
        url:"users/getData",
        type:"POST",
        data: ({
          ou: ou,
          nama:nama
        }),
        success:function(result){
            // console.log(result)
            result = JSON.parse(result)
            // console.log(result)
            if (result.count > 0) {
                // console.log(result.count === 1 && !(result[0]?.uid?.[0] ?? ""))
                if (result.count === 1 && !(result[0]?.uid?.[0] ?? "")){
                  return $('#tableuser').children('tbody:first').html('<tr><td colspan="10" class="text-center"><div class="alert alert-important" role="alert"><strong>Data kosong</strong></div></td></tr>');
                }
                let rows = "";

                const ou = (result[0]?.ou?.[0] ?? "")
                let n = 1
                for (let i = 0; i < result.count; i++) {
                    if (!(result[i]?.uid?.[0] ?? "")) continue
                    let str = (result[i]?.birthdate?.[0] ?? "");

                    // ambil bagian tanggal
                    let year = str.substring(0, 4);
                    let month = str.substring(4, 6);
                    let day = str.substring(6, 8);

                    // format ke d-m-y
                    let tgllahir = `${day}-${month}-${year}`;

                    rows += "<tr>";
                    rows += "<td>" + n + ".</td>";
                    rows += "<td>" + (result[i]?.uid?.[0] ?? "") + "</td>";
                    rows += "<td>" + (result[i]?.cn?.[0] ?? "") + "</td>";
                    rows += "<td>" + (result[i]?.employeenumber?.[0] ?? "") + "</td>";
                    rows += "<td>" + (result[i]?.departmentnumber?.[0] ?? "") + "</td>";
                    rows += "<td>" + tgllahir + "</td>";
                    // rows += "<td>" + (result[i]?.noktp?.[0] ?? "") + "</td>";
                    // rows += "<td>" + (result[i]?.firstnik?.[0] ?? "") + "</td>";
                    // rows += "<td>" + (result[i]?.lastnik?.[0] ?? "") + "</td>";
                    rows += "<td>" + (result[i]?.employeetype?.[0] ?? "") + "</td>";
                    rows += "<td>" + (result[i]?.description?.[0] ?? "") + "</td>";
                    rows += `<td><button data-ou='${ou}' data-id='${result[i]?.uid?.[0] ?? ""}' data-nama='${result[i]?.cn?.[0] ?? ""}' data-nik='${result[i]?.employeenumber?.[0] ?? ""}' data-divisi='${result[i]?.departmentnumber?.[0] ?? ""}'
                                 data-tgllahir='${tgllahir}' data-ktp='${result[i]?.noktp?.[0] ?? ""}' data-nikawal='${result[i]?.firstnik?.[0] ?? ""}' data-nikakhir='${result[i]?.lastnik?.[0] ?? ""}' data-ket='${result[i]?.description?.[0] ?? ""}' data-status='${result[i]?.employeetype?.[0] ?? ""}' onclick='edit(this)' class='btn btn-1'><i class='fa fa-edit'></i></button>
                                <button class='btn btn-1' onclick='btnHapus("${result[i]?.sn?.[0] ?? ""}","${result[i]?.uid?.[0] ?? ""}","${ou}")'><i class='fa fa-trash'></i></button>
                              </td>`;
                    rows += "</tr>";
                    n++
                }
                
                if(tableData !== undefined){
                    tableData.destroy()
                }

                $('#tableuser').children('tbody:first').html(rows);
                initDtDetail()
            } else {
                $('#tableuser').children('tbody:first').html('<tr><td colspan="10" class="text-center"><div class="alert alert-important" role="alert"><strong>Data kosong</strong></div></td></tr>');
            }
        }
    })
}

function btnHapus(nama,uid,ou){
  $('.btnDel').removeAttr('onclick');
  $('.btnDel').attr('onclick', `hapus('${uid}','${ou}')`);
  $('.namaHapus').text(nama)
  $('#modal-delete').modal('show');
}

function hapus(uid,ou){
  $.ajax({
        url:"users/hapus",
        type:"POST",
        data: ({
          uid: uid,
          ou: ou,
        }),
        success:function(result){
          console.log(result)
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

          getData(1,ou)
        }
    })
}

function save(mode){
  const idLama = $('.idLama').val()
  const id = $('.id').val()
  const nama = $('.nama').val()
  const username = $('.username').val()
  const password = $('.password').val()
  const email = $('.email').val()
  const ouLama = $('.ou').val()
  const ou = $('.formOu').val()

  const url = mode === 1 ? "users/ubah" : "users/tambah";

  $.ajax({
        url:url,
        type:"POST",
        data: ({
              idLama: idLama,
              id: id,
              nama: nama,
              username: username,
              password: password,
              email:email,
              ouLama:ouLama,
              ou:ou
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
          getData(1,ou)
        }
    })
}

function edit(el) {
    $('.btnSave').removeAttr('onclick');
    $('.btnSave').attr('onclick', 'save(1)');

    let $btn = $(el);

    let id       = $btn.data("id");
    let nama     = $btn.data("nama");
    let nik    = $btn.data("nik");
    let divisi    = $btn.data("divisi");
    let tgllahir    = $btn.data("tgllahir");
    let ktp    = $btn.data("ktp");
    let nikawal    = $btn.data("nikawal");
    let nikakhir    = $btn.data("nikakhir");
    let status    = $btn.data("status");
    let ket    = $btn.data("ket");

    // Isi ke form edit
    $(".idLama").val(id);
    $(".id").val(id);
    $(".nama").val(nama);
    $(".nik").val(nik);
    $(".divisi").val(divisi);
    $(".tgllahir").val(tgllahir);
    $(".ktp").val(ktp);
    $(".nikawal").val(nikawal);
    $(".nikakhir").val(nikakhir);
    $(".statuss").val(status);
    $(".ket").val(ket);

    $('.password').val('')
    $('.modal-title').text('Ubah User')
    $("#modaladd").modal('show');
}

function tambah(){
  $('.btnSave').removeAttr('onclick');
  $('.btnSave').attr('onclick', 'save(2)');

  $('.id').val('')
  $('.nama').val('')
  $('.username').val('')
  $('.password').val('')
  $('.email').val('')
  $('.formOu').val('')
  $('.modal-title').text('Tambah User')
}

function initDtDetail() {
    tableData = $('#tableuser').DataTable({
        ordering: false,
        autoWidth: false, // supaya tidak auto hitung width
        columnDefs: [
            { targets: 0, width: "3%", className: "text-center" }, // No.
            { targets: 1, width: "10%", className: "text-center" },  // ID
            { targets: 2, width: "20%", className: "text-left" },  // Nama
            { targets: 3, width: "10%", className: "text-center" },  // NIK
            { targets: 4, width: "10%", className: "text-center" },  // Divisi
            { targets: 5, width: "10%", className: "text-center" },// Tgl Lahir
            { targets: 6, width: "5%", className: "text-center" }, // Status
            { targets: 7, width: "20%", className: "text-left" },  // Ket
            { targets: 8, width: "12%", className: "text-center"}  // Action
        ],
        drawCallback: function(settings) {
            let api = this.api();
            let rows = api.rows({ filter: 'applied' }).data().length;

            if (rows === 0) {
                $('#tableuser tbody').html(
                    '<tr><td colspan="13" style="text-align: center;">' +
                    '<div class="alert alert-success" role="alert" style="margin-bottom: 0;">' +
                    '<strong>Data Kosong</strong></div></td></tr>'
                );
                $('.dataTables_paginate').hide();
            } else {
                $('.dataTables_paginate').show();
            }
        }
    });
}


