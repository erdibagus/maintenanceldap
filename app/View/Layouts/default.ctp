<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
	<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
	<title>LDAP</title>
	<!-- CSS files -->
<link href="./dist/css/tabler.min.css" rel="stylesheet"/>
<?php 
	echo $this->Html->meta('icon','/img/berno1.png'); 
	echo $this->Html->css('/css/font-awesome.min');
	echo $this->Html->css('style');
    echo $this->Html->css('sweatalert');
    echo $this->Html->css('jquery-ui');
	echo $this->Html->css('/dist/DataTables/datatables.min');
	echo $this->Html->script('jquery.min');
	echo $this->Html->script('jquery-ui.min');
	echo $this->Html->script('sweatalert');
	echo $this->Html->script('/dist/DataTables/datatables.min');
	$path = WWW_ROOT . 'js' . DS . 'function.js';
    $versi = file_exists($path) ? filemtime($path) : time();
    echo $this->Html->script("function.js?v=$versi");
?>
<script src="https://unpkg.com/htmx.org@1.9.10"></script>

</head>
<body>
	<div id="loading" style="display: none">
    	<img src="img/loading.gif" alt="Loading...">
	</div> 
	<div class="page" style="background: linear-gradient(rgba(255, 255, 255, 0.98), rgba(255,255,255,0.9)), url('img/bg-01.jpg'); 
            background-size: cover; 
            background-position: center;">
		<!-- Sidebar -->
		<aside class="navbar navbar-vertical navbar-expand-lg">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="navbar-brand text-center navbar-brand-autodark">
					<a href="#">
						<img src="./img/logoBerno.png" width="170vh">
					</a>
				</div>
				
				<div class="collapse navbar-collapse" id="sidebar-menu">
					<ul class="navbar-nav pt-lg-3">
						<li class="nav-item">
							<a class="nav-link"
								hx-get="./homes"
								hx-target="#main-content"
								hx-push-url="true">
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
								</span>
								<span class="nav-link-title">
									Home
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link"
								hx-get="./groups"
								hx-target="#main-content"
								hx-push-url="true">
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-buildings"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15" /><path d="M16 8h2c1 0 2 1 2 2v11" /><path d="M3 21h18" /><path d="M10 12v0" /><path d="M10 16v0" /><path d="M10 8v0" /><path d="M7 12v0" /><path d="M7 16v0" /><path d="M7 8v0" /><path d="M17 12v0" /><path d="M17 16v0" /></svg>
								</span>
								<span class="nav-link-title">
									Group
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link"
								hx-get="./users"
								hx-target="#main-content"
								hx-push-url="true">
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
								</span>
								<span class="nav-link-title">
									User
								</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</aside>
		<!-- Navbar -->
		<header class="navbar navbar-expand-md sticky-top d-none d-lg-flex" >
			<div class="container-xl">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
			<div class="navbar-nav flex-row order-md-last">
				<div class="nav-item dropdown">
					<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
						<span class="avatar avatar-sm" style="background-image: url(./img/monster.png)"></span>
						<div class="d-none d-xl-block ps-2">
							<div>Admin</div>
							<div class="mt-1 small text-secondary">404</div>
						</div>
					</a>
					<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
						<a href="#" class="dropdown-item">Logout</a>
					</div>
				</div>
			</div>
			<div class="collapse navbar-collapse" id="navbar-menu">
				<div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
				</div>
			</div>
		</header>
	<div class="page-wrapper" id="main-content">
		<?php echo $this->fetch('content'); ?>
	</div>
	<div class="page-wrapper">
		<footer class="footer footer-transparent p-0 mb-2">
			<div class="container-xl">
				<div class="row align-items-center flex-row-reverse">
					<div class="col-12 mt-3 mt-lg-0">
						<ul class="list-inline list-inline-dots mb-0">
							<li class="list-inline-item">
								Copyright &copy; <?php echo date('Y'); ?>
								LDAP Admin Bernofarm.
							</li>
						</ul>
					</div>
				</div>
			</div>
		</footer>
	</div>
	
			<!-- Tabler Core -->
<script src="./dist/js/tabler.min.js" defer></script>
</body>
</html>
