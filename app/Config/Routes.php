<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->group('/', function ($routes) 
{
    $routes->add('logout', 'Admin\Admin::logout');
    $routes->add('userguide', 'Admin\Admin::userguide');
    $routes->get('user', 'Admin\Admin::user');
    $routes->get('treasury', 'Admin\Admin::treasury');
    $routes->get('gs', 'Admin\Admin::gs');
    $routes->add('log_pdln_t', 'Admin\log_pdln_t::log_pdln_t');
});

// '/', ['filter'=> 'auth'], function ($routes)
$routes->group('/', ['filter'=> 'auth'], function ($routes) 
{
    $routes->add('superuser', 'Admin\Admin::superuser');
    $routes->add('sukses', 'Admin\Admin::sukses');

    $routes->add('dashboard/(:num)', 'Admin\Dashboard::dashboard/$1');
    $routes->add('support/(:num)', 'Admin\Dashboard::support/$1');
    $routes->post('importsupport/(:num)', 'Admin\Dashboard::importsupport/$1');
    $routes->post('importbiaya/(:num)', 'Admin\Dashboard::importbiaya/$1');
    $routes->get('exportlaporan/(:num)', 'Admin\Dashboard::exportlaporan/$1');
    $routes->get('exportbiaya/(:num)', 'Admin\Dashboard::exportbiaya/$1');
    $routes->get('exporterp/(:num)', 'Admin\Dashboard::exporterp/$1');
    $routes->get('biayaxls', 'Admin\Dashboard::biayaxls');
    $routes->get('biayaxlsx', 'Admin\Dashboard::biayaxlsx');
    $routes->get('supportxls', 'Admin\Dashboard::supportxls');
    $routes->add('editbiayasupport/(:any)', 'Admin\Dashboard::editbiayasupport/$1/$2/$3/$4');
    $routes->add('gsselesaisupport/(:any)', 'Admin\Dashboard::gsselesaisupport/$1/$2');

    $routes->add('transaksi', 'Admin\Transaksi::transaksi');
    $routes->add('tambahdataid', 'Admin\Transaksi::tambahdataid');
    $routes->add('islogin/(:num)', 'Admin\Transaksi::islogin/$1');
    $routes->add('detailtransaksi/(:num)', 'Admin\Transaksi::detailtransaksi/$1');
    $routes->add('tambahpersonil/(:num)', 'Admin\Transaksi::tambahpersonil/$1');
    $routes->add('tambahnegara/(:num)', 'Admin\Transaksi::tambahnegara/$1');

    $routes->add('datapjum/(:any)', 'Admin\PJUM::datapjum/$1/$2/$3');
    $routes->add('listpjum/(:any)', 'Admin\PJUM::listpjum/$1/$2');

    $routes->get('exportpjum/(:any)', 'Admin\PJUM::exportpjum/$1/$2/$3');
    $routes->post('importpjum/(:any)', 'Admin\PJUM::importpjum/$1/$2/$3');

    $routes->get('getrequest', 'Admin\PJUM::getrequest');

    $routes->add('editbiayapjum/(:any)', 'Admin\PJUM::editbiayapjum/$1/$2/$3/$4/$5');
    $routes->add('editbiayapb/(:any)', 'Admin\PB::editbiayapb/$1/$2/$3/$4/$5');

    $routes->add('editnopjum/(:any)', 'Admin\PJUM::editnopjum/$1/$2/$3');
    $routes->add('editnopb/(:any)', 'Admin\PB::editnopb/$1/$2/$3');

    $routes->add('tanggalpjum/(:any)', 'Admin\PJUM::tanggalpjum/$1/$2/$3');
    $routes->add('tanggalpb/(:any)', 'Admin\PB::tanggalpb/$1/$2/$3');

    $routes->add('editbiayapum/(:any)', 'Admin\PJUM::editbiayapum/$1/$2/$3/$4');
    $routes->add('editbiayasisa/(:any)', 'Admin\PJUM::editbiayasisa/$1/$2/$3/$4');

    $routes->add('kurspjum/(:any)', 'Admin\PJUM::kurspjum/$1/$2/$3');
    $routes->add('kurspb/(:any)', 'Admin\PB::kurspb/$1/$2/$3');

    $routes->add('editkurspjum/(:any)', 'Admin\PJUM::editkurspjum/$1/$2/$3/$4');
    $routes->add('editkurspb/(:any)', 'Admin\PB::editkurspb/$1/$2/$3/$4');

    $routes->add('treasurypjum/(:any)', 'Admin\PJUM::treasurypjum/$1/$2/$3/$4');
    $routes->add('treasurypb/(:any)', 'Admin\PB::treasurypb/$1/$2/$3/$4');

    $routes->add('treasuryselesaipjum/(:any)', 'Admin\PJUM::treasuryselesaipjum/$1/$2');
    $routes->add('treasuryselesaipb/(:any)', 'Admin\PB::treasuryselesaipb/$1/$2');

    $routes->add('gsselesaipjum/(:any)', 'Admin\PJUM::gsselesaipjum/$1/$2');
    $routes->add('gsselesaipb/(:any)', 'Admin\PB::gsselesaipb/$1/$2');

    $routes->add('datapb/(:any)', 'Admin\PB::datapb/$1/$2/$3');
    $routes->add('listpb/(:any)', 'Admin\PB::listpb/$1/$2');

    $routes->get('exportpb/(:any)', 'Admin\PB::exportpb/$1/$2/$3');
    $routes->post('importpb/(:any)', 'Admin\PB::importpb/$1/$2/$3');
});

// '/', ['filter'=> 'noauth'], function ($routes)
$routes->group('/', ['filter'=> 'noauth'], function ($routes) {
    $routes->add('', 'Admin\Admin::login');
    $routes->add('ci4', 'Home::index');
});

$routes->match(['get', 'post'], 'email', 'SendEmail::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
