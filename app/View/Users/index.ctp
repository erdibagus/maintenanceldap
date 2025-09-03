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
					<label class="col-3 col-form-label">Nama</label>
					<div class="col">
						<input type="text" class="form-control filterNama" placeholder="Input Nama">
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
					<button class="btn btn-6 btn-primary" data-bs-toggle="modal" data-bs-target="#modaladd" onclick="tambah()">
					ADD
					</button>
				</div>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table id="tableuser" class="table table-vcenter card-table table-hover border">
						<thead>
							<tr class="table-dark">
								<th>DN</th>
								<th>ID</th>
								<th>Username</th>
								<th>Nama</th>
								<th>Email</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
                                <td colspan="10" class="text-center"><div class="alert alert-important" role="alert"><strong>Data kosong</strong></div></td>
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
				<div class="card-body">
					<input type="hidden" class="form-control idLama">
					<input type="hidden" class="form-control ou">
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">ID</label>
						<div class="col">
							<input type="text" class="form-control id" placeholder="ID">
						</div>
					</div>
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">Name</label>
						<div class="col">
							<input type="text" class="form-control nama" placeholder="Name">
						</div>
					</div>
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">Email address</label>
						<div class="col">
							<input type="email" class="form-control email" aria-describedby="emailHelp" placeholder="Email">
							<small class="form-hint">We'll never share your email with anyone else.</small>
						</div>
					</div>
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">Group</label>
						<div class="col">
							<select class="form-select formOu">
							</select>
						</div>
					</div>
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">Username</label>
						<div class="col">
							<input type="text" class="form-control username" placeholder="Username">
						</div>
					</div>
					<div class="mb-3 row">
						<div class="col">
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
					</div>
					<div class="mb-3 row">
						<label class="col-3 col-form-label">Password</label>
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