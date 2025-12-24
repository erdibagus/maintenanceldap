<?php 
	$path = WWW_ROOT . 'js' . DS . 'h1user.js';
    $versi = file_exists($path) ? filemtime($path) : time();
    echo $this->Html->script("h1user.js?v=$versi");
?>
<div class="page-header">
	<div class="container-xl">
		<h2>Data User</h2>
		<div class="card">
			<div class="card-header">
				<div><i class="fa fa-search fa-fw"></i> Filter</div>
			</div>
			<div class="card-body">
				<div class="col-lg-3 mb-3 row">
					<label class="col-3 col-form-label">OU</label>
					<div class="col">
						<select class="form-select ou">
						</select>
					</div>
				</div>
				<div class="col-lg-3 mb-3 row">
					<label class="col-3 col-form-label">Akses</label>
					<div class="col">
						<select class="form-select filterAkses">
							<option value="">Pilih..</option>
							<option value="jkt">Jakarta</option>
							<option value="sda">Sidoharjo</option>
							<option value="all">All</option>
						</select>
					</div>
				</div>
				<div class="col-lg-3 mb-3 row">
					<label class="col-3 col-form-label">Nama</label>
					<div class="col">
						<input type="text" class="form-control filterNama" placeholder="Nama">
					</div>
				</div>
			</div>
			<div class="card-footer">
				<button type="submit" class="btn btn-outline-dark" onclick="getData(0)">Cari</button>
			</div>
		</div>
	</div>
</div>
<div class="page-body">
	<div class="container-xl">
		<div class="card">
			<div class="card-header card-header-light">
				<div><i class="fa fa-th-list fa-fw"></i> List Data</div>
				<div class="card-actions">
					<button class="btn btn-6 btn-primary btnAdd" data-bs-toggle="modal" data-bs-target="#modaladd" onclick="tambah()">
					Tambah disini
					</button>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table id="tableuser" class="table table-vcenter card-table table-hover border">
						<thead>
							<tr class="table-dark">
								<th>No.</th>
								<th>ID</th>
								<th>Nama</th>
								<th>NIK</th>
								<th>Divisi</th>
								<th>Tgl Lahir</th>
								<!-- <th style="width:5%">No. KTP</th>
								<th style="width:5%">Nik Awal</th>
								<th style="width:5%">Nik Akhir</th> -->
								<th>Status</th>
								<th>Email</th>
								<th><i class="fa fa-gears"></i></th>
							</tr>
						</thead>
						<tbody>
							<tr>
                                <td colspan="20" class="text-center"><div class="alert alert-important" role="alert"><strong>Data kosong</strong></div></td>
                            </tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-blur fade" id="modaladd" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row row-cards">
					<input type="hidden" class="form-control dn" disabled>
					<div class="col-md-4">
						<div class="mb-1">
							<label class="form-label">ID</label>
							<input type="text" class="form-control id" placeholder="ID" disabled>
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-1">
							<label class="form-label">Ou</label>
							<select class="form-control form-select group" disabled>
								<option value="jkt">Jakarta</option>
								<option value="sda">Sidoharjo</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-1">
							<label class="form-label">Akses</label>
							<select class="form-control form-select akses">>
								<option value="jkt">Jakarta</option>
								<option value="sda">Sidoharjo</option>
								<option value="all">All</option>
							</select>
						</div>
					</div>
					<div class="col-sm-7 col-md-8">
						<div class="mb-1">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control nama" placeholder="Nama">
						</div>
					</div>
					<div class="col-sm-5 col-md-4">
						<div class="mb-1">
							<label class="form-label">NIK</label>
							<input type="text" class="form-control nik" placeholder="NIK">
						</div>
					</div>
					<div class="col-sm-6 col-md-6">
						<div class="mb-1">
							<label class="form-label">Divisi</label>
							<input type="text" class="form-control divisi" placeholder="Divisi">
						</div>
					</div>
					<div class="col-sm-6 col-md-6">
						<div class="mb-1">
							<label class="form-label">Tgl Lahir</label>
							<input type="text" class="form-control tgllahir" placeholder="Tgl Lahir">
						</div>
					</div>
					<div class="col-sm-6 col-md-6">
						<div class="mb-1">
							<label class="form-label">No. KTP</label>
							<input type="text" class="form-control ktp" placeholder="No. KTP">
						</div>
					</div>
					<div class="col-sm-6 col-md-6">
						<div class="mb-1">
							<label class="form-label">Email</label>
							<input type="email" class="form-control email" placeholder="Email">
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="mb-1">
							<label class="form-label">NIK Awal</label>
							<input type="text" class="form-control nikawal" placeholder="NIK Awal">
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="mb-1">
							<label class="form-label">NIK Akhir</label>
							<input type="test" class="form-control nikakhir" placeholder="NIK Akhir">
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-1">
							<label class="form-label">Status</label>
							<select class="form-control form-select statuss">
								<option value="aktif">Aktif</option>
								<option value="nonaktif">Non Aktif</option>
							</select>
						</div>
					</div>
					<div class="col-md-12">
						<div class="mb-1 mb-0">
							<label class="form-label">Keterangan</label>
							<textarea rows="5" class="form-control ket" placeholder="Keterangan"></textarea>
						</div>
					</div>
					<div class="col-md-12">
						<div class="mb-1">
							<label class="form-label">Email Perusahaan</label>
							<div class="bernoMail">
								
							</div>
							<button class="btn btn-6 btn-success mb-1" id="btnTambah">+</button>
						</div>
					</div>

					<div class="col-md-12 alertP">
						<div class="alert alert-warning" role="alert">
							<div class="d-flex">
								<div>
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon icon-2"><path d="M12 9v4"></path><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path><path d="M12 16h.01"></path></svg>
								</div>
								<div>
									<h4 class="alert-title">Kosongkan jika tidak ingin mengubah password!</h4>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 mt-0">
						<div class="mb-1">
							<label class="form-label">Password</label>
							<div class="col">
								<input type="text" class="form-control password" placeholder="Password">
								<small class="form-hint">
									Your password must be 8-20 characters long, contain letters and numbers, and must not contain
									spaces, special characters, or emoji.
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn btn-outline-secondary" data-bs-dismiss="modal">
				Cancel
				</a>
				<a href="#" class="btn btn-primary btnSave">
				Save
				</a>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
		<div class="modal-content">
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			<div class="modal-status bg-danger"></div>
			<div class="modal-body text-center py-4">
				<!-- Download SVG icon from http://tabler.io/icons/icon/alert-triangle -->
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon mb-2 text-danger icon-lg"><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
				<h3>Are you sure?</h3>
				<div class="text-secondary">Do you really want to remove <span class="fw-bold namaHapus"></span>? What you've done cannot be undone.</div>
			</div>
			<div class="modal-footer">
				<div class="w-100">
					<div class="row">
						<div class="col">
							<a href="#" class="btn btn-3 w-100" data-bs-dismiss="modal">
							Cancel
							</a>
						</div>
						<div class="col">
							<button class="btn btn-danger btn-4 w-100 btnDel" data-bs-dismiss="modal">
							Delete
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>