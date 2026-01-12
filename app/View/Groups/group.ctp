<?php 
	$path = WWW_ROOT . 'js' . DS . 'h1group.js';
    $versi = file_exists($path) ? filemtime($path) : time();
    echo $this->Html->script("h1group.js?v=$versi");
?>
<div class="page-header">
	<div class="container-xl">
		<h2>Data Group</h2>
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
					<table id="tableData" class="table table-vcenter card-table table-hover border">
						<thead>
							<tr class="table-dark">
								<th>DN</th>
								<th>Nama</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
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
					<input type="hidden" class="form-control lama">
					<div class="mb-3 row">
						<label class="col-3 col-form-label required">Nama</label>
						<div class="col">
							<input type="text" class="form-control baru" placeholder="Nama">
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