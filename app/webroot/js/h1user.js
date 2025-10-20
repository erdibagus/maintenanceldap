let tableData, listMail = []

$(document).ready(function(){
    getGroup()
    $(".tgllahir").datepicker({
        dateFormat: "dd/mm/yy",   
        changeMonth: true,        
        changeYear: true,         
        maxDate: 0,
        yearRange: "1975:+0"
    });
    $('#btnTambah').on('click', function() {
      const baru = `
        <div class="input-group mb-1">
          <input type="text" class="form-control" placeholder="Email baru">
          <button class="btn btn-danger btnHapus" type="button">
            -
          </button>
        </div>`;
      $('.bernoMail').append(baru);
    });
})
$(document).on('click', '.btnHapus', function() {
    $(this).closest('.input-group').remove();
});
function getGroup(){
  $.ajax({
        url:"groups/getData",
        success:function(result){
            result = JSON.parse(result)
            // console.log(result)
            if (result.count > 0) {
              let rows = "<option value=''>Pilih..</option>";

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
  const akses = $('.filterAkses').val()

  if(mode == 1){
    ou = ouSave
  } else {
    // if (!ou) return toastMixin.fire({
    //                   icon: 'warning',
    //                   title: 'Pilih OU.'
    //               });
  }

  $.ajax({
        url:"users/getData",
        type:"POST",
        data: ({
          ou: ou,
          nama:nama,
          akses:akses,
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
                    let tgllahir = `${day}/${month}/${year}`;

                    //bernoMail
                    const bernoMail = result[i].bernomail
                    // console.log(bernoMail)
                    let arrMail = [];
                    for (let key in bernoMail) {
                      if (key !== "count") { 
                        arrMail.push(bernoMail[key]);
                      }
                    }

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
                    rows += "<td>" + (result[i]?.mail?.[0] ?? "") + "</td>";
                    rows += `<td><button data-dn='${result[i]?.dn ?? ""}' data-email='${result[i]?.mail?.[0] ?? ""}' data-ou='${result[i]?.ou?.[0] ?? ""}' data-id='${result[i]?.uid?.[0] ?? ""}' data-nama='${result[i]?.cn?.[0] ?? ""}' data-nik='${result[i]?.employeenumber?.[0] ?? ""}' data-divisi='${result[i]?.departmentnumber?.[0] ?? ""}'
                                 data-tgllahir='${tgllahir}' data-ktp='${result[i]?.noktp?.[0] ?? ""}' data-nikawal='${result[i]?.firstnik?.[0] ?? ""}' data-nikakhir='${result[i]?.lastnik?.[0] ?? ""}' data-ket='${result[i]?.description?.[0] ?? ""}' data-status='${result[i]?.employeetype?.[0] ?? ""}' data-bernoMail='${arrMail.join(',')}' onclick='edit(this)' class='btn'><i class='fa fa-edit'></i></button>
                                <button class='btn btn-outline-danger' onclick='btnHapus("${result[i]?.cn?.[0] ?? ""}","${result[i]?.dn ?? ""}")'><i class='fa fa-trash'></i></button>
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

function btnHapus(nama,dn){
  $('.btnDel').removeAttr('onclick');
  $('.btnDel').attr('onclick', `hapus('${dn}')`);
  $('.namaHapus').text(nama)
  $('#modal-delete').modal('show');
}

function hapus(dn){
  $.ajax({
        url:"users/hapus",
        type:"POST",
        data: ({
          dn: dn
        }),
        success:function(result){
          const text = result.replace(/\s+/g, '');
          if(text == 'sukses'){
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
          const ouDn = dn.split(',').map(s => s.trim()).find(s => s.startsWith('ou='))?.slice(3) || '';
          getData(1,ouDn)
        }
    })
}

function save(mode){
  const dn = $('.dn').val()
  const id = $('.id').val()
  const nama = $('.nama').val()
  const nik = $('.nik').val()
  const divisi = $('.divisi').val()
  const tgllahir = $('.tgllahir').val()
  const ktp = $('.ktp').val()
  const nikawal = $('.nikawal').val()
  const nikakhir = $('.nikakhir').val()
  const status = $('.statuss').val()
  const ket = $('.ket').val()
  const ou = $('.group').val()
  const akses = $('.akses').val()
  const email = $('.email').val()
  const pass = $('.password').val()

  if (!id || !nama || !nik || !divisi || !tgllahir || !ktp || !nikawal || !nikakhir || !status || !ket || !ou) {
      toastMixin.fire({
                icon: 'error',
                title: 'Semua field harus diisi!'
            });
      return false; 
  }

  const url = mode === 1 ? "users/ubah" : "users/tambah";
  const tgl = dateLDAP(tgllahir)

  let formMail = [], addMail = [], delMail = []

  $('.bernoMail input.form-control').each(function() {
      const val = $(this).val().trim();
      if (val !== '') {
          formMail.push(val);
      }
  });

  // console.log(listMail);return

  addMail = formMail.filter(item => !listMail.includes(item))

  delMail = listMail.filter(item => !formMail.includes(item))

  // console.log(addMail,delMail);return

  $.ajax({
        url:url,
        type:"POST",
        data: ({
              dn: dn,
              id: id,
              nama: nama,
              nik: nik,
              divisi:divisi,
              tgllahir:tgl,
              ktp:ktp,
              nikawal:nikawal,
              nikakhir:nikakhir,
              status:status,
              ket:ket,
              ou:ou,
              akses:akses,
              email:email,
              password: pass,
              addMail: addMail,
              delMail: delMail
            }),
        success:function(result){
          console.log(result)
          // console.log(JSON.parse(result));
          const text = JSON.parse(result);
          if(text.status == 'success'){
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
    $('.id').prop('disabled',true);
    $('.group').prop('disabled',true);
    $('.btnSave').removeAttr('onclick');
    $('.btnSave').attr('onclick', 'save(1)');

    $('.alertP').removeClass('d-none')

    $('.bernoMail').html('');

    listMail = []

    let $btn = $(el);

    let dn       = $btn.data("dn");
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
    let ou    = $btn.data("ou");
    const ouDn = dn.split(',').map(s => s.trim()).find(s => s.startsWith('ou='))?.slice(3) || '';
    let email    = $btn.data("email");
    let bernoMail    = $btn.data("bernomail");

    // Isi ke form edit
    $(".dn").val(dn);
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
    $(".akses").val(ou);
    $(".group").val(ouDn);
    $(".email").val(email);

    //bernoMail
    // console.log(bernoMail);return
    if(bernoMail){
        const arrMail = bernoMail.split(',')
        listMail = arrMail
        let txtMail = ""
        arrMail.forEach(item => {
            txtMail += `<div class="input-group mb-1">
                        <input type="text" disabled class="form-control" value="${item}">
                        <button class="btn btn-danger btnHapus" type="button">
                          -
                        </button>
                      </div>`
        });

        $(".bernoMail").html(txtMail)
    }

    $('.password').val('')
    $('.modal-title').text('Ubah User')
    $("#modaladd").modal('show');
}

function tambah(){
  $('.id').prop('disabled',false);
  $('.group').prop('disabled',false);
  $('.btnSave').removeAttr('onclick');
  $('.btnSave').attr('onclick', 'save(2)');

  $('.alertP').addClass('d-none')

  listMail = []

  $(".dn").val("");
  $(".id").val("");
  $(".nama").val("");
  $(".nik").val("");
  $(".divisi").val("");
  $(".tgllahir").val("");
  $(".ktp").val("");
  $(".nikawal").val("");
  $(".nikakhir").val("");
  $(".statuss").val("");
  $(".ket").val("");
  $(".group").val("");
  $(".akses").val("");
  $(".email").val("");
  $('.password').val('')
  $('.bernoMail').html('');

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
            { targets: 3, width: "5%", className: "text-center" },  // NIK
            { targets: 4, width: "10%", className: "text-center" },  // Divisi
            { targets: 5, width: "10%", className: "text-center" },// Tgl Lahir
            { targets: 6, width: "5%", className: "text-center" }, // Status
            { targets: 7, width: "20%", className: "text-left" },  // Ket
            { targets: 8, width: "17%", className: "text-center"}  // Action
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

function dateLDAP(tgl){
  const [hari, bulan, tahun] = tgl.split("/");

  const dateUTC = new Date(Date.UTC(tahun, bulan - 1, hari));

  const YYYY = dateUTC.getUTCFullYear();
  const MM = String(dateUTC.getUTCMonth() + 1).padStart(2, "0");
  const DD = String(dateUTC.getUTCDate()).padStart(2, "0");
  const HH = String(dateUTC.getUTCHours()).padStart(2, "0");
  const min = String(dateUTC.getUTCMinutes()).padStart(2, "0");
  const SS = String(dateUTC.getUTCSeconds()).padStart(2, "0");

  return `${YYYY}${MM}${DD}${HH}${min}${SS}Z`;
}
